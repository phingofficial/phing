<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

declare(strict_types=1);

use Phing\Exception\BuildException;
use Phing\Io\FileReader;
use Phing\Io\FileWriter;
use Phing\Io\File;
use Phing\Project;
use function Jawira\PlantUml\encodep;

/**
 * Class VisualizerTask
 *
 * VisualizerTask creates diagrams using buildfiles, these diagrams represents calls and depends among targets.
 *
 * @author Jawira Portugal
 */
class VisualizerTask extends HttpTask
{
    public const FORMAT_EPS = 'eps';
    public const FORMAT_PNG = 'png';
    public const FORMAT_PUML = 'puml';
    public const FORMAT_SVG = 'svg';
    public const SERVER = 'http://www.plantuml.com/plantuml';
    public const STATUS_OK = 200;
    public const XSL_CALLS = __DIR__ . '/calls.xsl';
    public const XSL_FOOTER = __DIR__ . '/footer.xsl';
    public const XSL_HEADER = __DIR__ . '/header.xsl';
    public const XSL_TARGETS = __DIR__ . '/targets.xsl';

    /**
     * @var string Diagram format
     */
    protected $format;

    /**
     * @var string Location in disk where diagram is saved
     */
    protected $destination;

    /**
     * @var string PlantUml server
     */
    protected $server;

    /**
     * Setting some default values and checking requirements
     */
    public function init(): void
    {
        parent::init();
        if (!function_exists('\Jawira\PlantUml\encodep')) {
            $exceptionMessage = get_class($this) . ' requires "jawira/plantuml-encoding" library';
        }
        if (!class_exists(XSLTProcessor::class)) {
            $exceptionMessage = get_class($this) . ' requires XSL extension';
        }
        if (!class_exists(SimpleXMLElement::class)) {
            $exceptionMessage = get_class($this) . ' requires SimpleXML extension';
        }
        if (isset($exceptionMessage)) {
            $this->log($exceptionMessage, Project::MSG_ERR);
            throw new BuildException($exceptionMessage);
        }
        $this->setFormat(VisualizerTask::FORMAT_PNG);
        $this->setServer(VisualizerTask::SERVER);
    }

    /**
     * The main entry point method.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Phing\Io\IOException
     * @throws \Phing\Exception\NullPointerException
     */
    public function main(): void
    {
        $pumlDiagram = $this->generatePumlDiagram();
        $destination = $this->resolveImageDestination();
        $format = $this->getFormat();
        $image = $this->generateImage($pumlDiagram, $format);
        $this->saveToFile($image, $destination);
    }

    /**
     * Retrieves loaded buildfiles and generates a PlantUML diagram
     *
     * @return string
     */
    protected function generatePumlDiagram(): string
    {
        /**
         * @var \Phing\Parser\XmlContext $xmlContext
         */
        $xmlContext = $this->getProject()
            ->getReference("phing.parsing.context");
        $importStack = $xmlContext->getImportStack();
        $pumlDiagram = $this->generatePuml($importStack);

        return $pumlDiagram;
    }

    /**
     * Read through provided buildfiles and generates a PlantUML diagram
     *
     * @param \Phing\Io\File[] $buildFiles
     *
     * @return string
     */
    protected function generatePuml(array $buildFiles): string
    {
        $puml = $this->transformToPuml(reset($buildFiles), VisualizerTask::XSL_HEADER);

        /**
         * @var \Phing\Io\File $buildFile
         */
        foreach ($buildFiles as $buildFile) {
            $puml .= $this->transformToPuml($buildFile, VisualizerTask::XSL_TARGETS);
        }

        foreach ($buildFiles as $buildFile) {
            $puml .= $this->transformToPuml($buildFile, VisualizerTask::XSL_CALLS);
        }

        $puml .= $this->transformToPuml(reset($buildFiles), VisualizerTask::XSL_FOOTER);

        return $puml;
    }

    /**
     * Transforms buildfile using provided xsl file
     *
     * @param \Phing\Io\File $buildfile Path to buildfile
     * @param string $xslFile XSLT file
     *
     * @return string
     */
    protected function transformToPuml(File $buildfile, string $xslFile): string
    {
        $xml = $this->loadXmlFile($buildfile->getPath());
        $xsl = $this->loadXmlFile($xslFile);

        $processor = new XSLTProcessor();
        $processor->importStylesheet($xsl);

        return $processor->transformToXml($xml) . PHP_EOL;
    }

    /**
     * Load XML content from a file
     *
     * @param string $xmlFile XML or XSLT file
     *
     * @return \SimpleXMLElement
     */
    protected function loadXmlFile(string $xmlFile): SimpleXMLElement
    {
        $xmlContent = (new FileReader($xmlFile))->read();
        $xml = simplexml_load_string($xmlContent);

        if (!($xml instanceof SimpleXMLElement)) {
            $message = "Error loading XML file: $xmlFile";
            $this->log($message, Project::MSG_ERR);
            throw new BuildException($message);
        }

        return $xml;
    }

    /**
     * Get the image's final location
     *
     * @return \Phing\Io\File
     * @throws \Phing\Io\IOException
     * @throws \Phing\Exception\NullPointerException
     */
    protected function resolveImageDestination(): File
    {
        $phingFile = $this->getProject()->getProperty('phing.file');
        $format = $this->getFormat();
        $candidate = $this->getDestination();
        $path = $this->resolveDestination($phingFile, $format, $candidate);

        return new File($path);
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Sets and validates diagram's format
     *
     * @param string $format
     *
     * @return VisualizerTask
     */
    public function setFormat(string $format): VisualizerTask
    {
        switch ($format) {
            case VisualizerTask::FORMAT_PUML:
            case VisualizerTask::FORMAT_PNG:
            case VisualizerTask::FORMAT_EPS:
            case VisualizerTask::FORMAT_SVG:
                $this->format = $format;
                break;
            default:
                $message = "'$format' is not a valid format";
                $this->log($message, Project::MSG_ERR);
                throw new BuildException($message);
                break;
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDestination(): ?string
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     *
     * @return VisualizerTask
     */
    public function setDestination(?string $destination): VisualizerTask
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Figure diagram's file path
     *
     * @param string $buildfilePath Path to main buildfile
     * @param string $format Extension to use
     * @param null|string $destination Desired destination provided by user
     *
     * @return string
     */
    protected function resolveDestination(string $buildfilePath, string $format, ?string $destination): string
    {
        $buildfileInfo = pathinfo($buildfilePath);

        // Fallback
        if (empty($destination)) {
            $destination = $buildfileInfo['dirname'];
        }

        // Adding filename if necessary
        if (is_dir($destination)) {
            $destination .= DIRECTORY_SEPARATOR . $buildfileInfo['filename'] . '.' . $format;
        }

        // Check if path is available
        if (!is_dir(dirname($destination))) {
            $message = "Directory '$destination' is invalid";
            $this->log($message, Project::MSG_ERR);
            throw new BuildException(sprintf($message, $destination));
        }

        return $destination;
    }

    /**
     * Generates an actual image using PlantUML code
     *
     * @param string $pumlDiagram
     * @param string $format
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function generateImage(string $pumlDiagram, string $format): string
    {
        if ($format === VisualizerTask::FORMAT_PUML) {
            $this->log('Bypassing, no need to call server', Project::MSG_DEBUG);

            return $pumlDiagram;
        }

        $format = $this->getFormat();
        $encodedPuml = encodep($pumlDiagram);
        $this->prepareImageUrl($format, $encodedPuml);

        $response = $this->request();
        $this->processResponse($response); // used for status validation

        return $response->getBody()->getContents();
    }

    /**
     * Prepares URL from where image will be downloaded
     *
     * @param string $format
     * @param string $encodedPuml
     */
    protected function prepareImageUrl(string $format, string $encodedPuml): void
    {
        $server = $this->getServer();
        $this->log("Server: $server", Project::MSG_VERBOSE);

        $server = filter_var($server, FILTER_VALIDATE_URL);
        if ($server === false) {
            $message = 'Invalid PlantUml server';
            $this->log($message, Project::MSG_ERR);
            throw new BuildException($message);
        }

        $imageUrl = sprintf('%s/%s/%s', rtrim($server, '/'), $format, $encodedPuml);
        $this->log($imageUrl, Project::MSG_DEBUG);
        $this->setUrl($imageUrl);
    }

    /**
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }

    /**
     * @param string $server
     *
     * @return VisualizerTask
     */
    public function setServer(string $server): VisualizerTask
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Receive server's response
     *
     * This method validates `$response`'s status
     *
     * @param \Psr\Http\Message\ResponseInterface $response Response from server
     *
     * @return void
     */
    protected function processResponse(\Psr\Http\Message\ResponseInterface $response): void
    {
        $status = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();
        $this->log("Response status: $status", Project::MSG_DEBUG);
        $this->log("Response reason: $reasonPhrase", Project::MSG_DEBUG);

        if ($status !== VisualizerTask::STATUS_OK) {
            $message = "Request unsuccessful. Response from server: $status $reasonPhrase";
            $this->log($message, Project::MSG_ERR);
            throw new BuildException($message);
        }
    }

    /**
     * Save provided $content string into $destination file
     *
     * @param string $content Content to save
     * @param \Phing\Io\File $destination Location where $content is saved
     *
     * @return void
     */
    protected function saveToFile(string $content, File $destination): void
    {
        $path = $destination->getPath();
        $this->log("Writing: $path", Project::MSG_INFO);

        (new FileWriter($destination))->write($content);
    }
}
