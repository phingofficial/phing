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

namespace Phing\Task\Ext;

use Jawira\PlantUmlClient\Client;
use Phing\Exception\BuildException;
use Phing\Io\FileReader;
use Phing\Io\FileWriter;
use Phing\Io\File;
use Phing\Project;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;
use XSLTProcessor;
use function array_reduce;
use function filter_var;
use function reset;
use function simplexml_load_string;
use const FILTER_VALIDATE_URL;

/**
 * Class VisualizerTask
 *
 * VisualizerTask creates diagrams using buildfiles, these diagrams represents calls and depends among targets.
 *
 * @author Jawira Portugal
 */
class VisualizerTask extends HttpTask
{
    public const FORMAT_EPS  = 'eps';
    public const FORMAT_PNG  = 'png';
    public const FORMAT_PUML = 'puml';
    public const FORMAT_SVG  = 'svg';
    public const SERVER      = 'http://www.plantuml.com/plantuml';
    public const STATUS_OK   = 200;
    public const XSL_CALLS   = __DIR__ . '/calls.xsl';
    public const XSL_FOOTER  = __DIR__ . '/footer.xsl';
    public const XSL_HEADER  = __DIR__ . '/header.xsl';
    public const XSL_TARGETS = __DIR__ . '/targets.xsl';

    /**
     * @var string Diagram format
     */
    protected $format;

    /**
     * @var null|string Location in disk where diagram is saved
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
        if (!class_exists(Client::class)) {
            $exceptionMessage = get_class($this) . ' requires "jawira/plantuml-client" library';
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
     * @throws \Phing\Io\IOException
     * @throws \Exception
     */
    public function main(): void
    {
        $pumlDiagram = $this->generatePumlDiagram();
        $destination = $this->resolveImageDestination();
        $format      = $this->getFormat();
        $image       = $this->generateImage($pumlDiagram, $format);
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
        $xmlContext  = $this->getProject()
                            ->getReference("phing.parsing.context");
        $importStack = $xmlContext->getImportStack();
        return $this->generatePuml($importStack);
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
        if (!($firstBuildFile = reset($buildFiles))) {
            $exceptionMessage = 'No buildfile to process';
            $this->log($exceptionMessage, Project::MSG_ERR);
            throw new BuildException($exceptionMessage);
        }

        $puml = $this->transformToPuml($firstBuildFile, VisualizerTask::XSL_HEADER);

        $puml = array_reduce($buildFiles, function (string $carry, File $buildFile) {
            return $carry . $this->transformToPuml($buildFile, VisualizerTask::XSL_CALLS);
        }, $puml);

        $puml = array_reduce($buildFiles, function (string $carry, File $buildFile) {
            return $carry . $this->transformToPuml($buildFile, VisualizerTask::XSL_TARGETS);
        }, $puml);

        $puml .= $this->transformToPuml($firstBuildFile, VisualizerTask::XSL_FOOTER);

        return $puml;
    }

    /**
     * Transforms buildfile using provided xsl file
     *
     * @param \Phing\Io\File $buildfile Path to buildfile
     * @param string         $xslFile   XSLT file
     *
     * @return string
     * @throws \Phing\Io\IOException
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
     * @throws \Phing\Io\IOException
     */
    protected function loadXmlFile(string $xmlFile): SimpleXMLElement
    {
        $xmlContent = (new FileReader($xmlFile))->read();
        $xml        = simplexml_load_string($xmlContent);

        if (!($xml instanceof SimpleXMLElement)) {
            $exceptionMessage = "Error loading XML file: $xmlFile";
            $this->log($exceptionMessage, Project::MSG_ERR);
            throw new BuildException($exceptionMessage);
        }

        return $xml;
    }

    /**
     * Get the image's final location
     *
     * @return \Phing\Io\File
     * @throws \Phing\Io\IOException
     */
    protected function resolveImageDestination(): File
    {
        $phingFile = $this->getProject()->getProperty('phing.file');
        $format    = $this->getFormat();
        $candidate = $this->getDestination();
        $path      = $this->resolveDestination($phingFile, $format, $candidate);

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
     * @return \Phing\Task\Ext\VisualizerTask
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
                $exceptionMessage = "'$format' is not a valid format";
                $this->log($exceptionMessage, Project::MSG_ERR);
                throw new BuildException($exceptionMessage);
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
     * @param null|string $destination
     *
     * @return \Phing\Task\Ext\VisualizerTask
     */
    public function setDestination(?string $destination): VisualizerTask
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Figure diagram's file path
     *
     * @param string      $buildfilePath Path to main buildfile
     * @param string      $format        Extension to use
     * @param null|string $destination   Desired destination provided by user
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
            $exceptionMessage = "Directory '$destination' is invalid";
            $this->log($exceptionMessage, Project::MSG_ERR);
            throw new BuildException(sprintf($exceptionMessage, $destination));
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
     * @throws \Exception
     */
    protected function generateImage(string $pumlDiagram, string $format): string
    {
        if ($format === VisualizerTask::FORMAT_PUML) {
            $this->log('Bypassing, no need to call server', Project::MSG_DEBUG);

            return $pumlDiagram;
        }

        $this->prepareImageUrl($pumlDiagram, $this->getFormat());
        $response = $this->request();
        $this->processResponse($response); // used for status validation

        return $response->getBody()->getContents();
    }

    /**
     * Prepares URL from where image will be downloaded
     *
     * @param string $pumlDiagram
     * @param string $format
     */
    protected function prepareImageUrl(string $pumlDiagram, string $format): void
    {
        $server = $this->getServer();
        $this->log("Server: $server", Project::MSG_VERBOSE);

        $imageUrl = (new Client())->setServer($server)
                                  ->generateUrl($pumlDiagram, $format);

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
     * @return \Phing\Task\Ext\VisualizerTask
     */
    public function setServer(string $server): VisualizerTask
    {
        if (!filter_var($server, FILTER_VALIDATE_URL)) {
            $exceptionMessage = 'Invalid PlantUml server';
            $this->log($exceptionMessage, Project::MSG_ERR);
            throw new BuildException($exceptionMessage);
        }
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
    protected function processResponse(ResponseInterface $response): void
    {
        $status       = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();
        $this->log("Response status: $status", Project::MSG_DEBUG);
        $this->log("Response reason: $reasonPhrase", Project::MSG_DEBUG);

        if ($status !== VisualizerTask::STATUS_OK) {
            $exceptionMessage = "Request unsuccessful. Response from server: $status $reasonPhrase";
            $this->log($exceptionMessage, Project::MSG_ERR);
            throw new BuildException($exceptionMessage);
        }
    }

    /**
     * Save provided $content string into $destination file
     *
     * @param string         $content     Content to save
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
