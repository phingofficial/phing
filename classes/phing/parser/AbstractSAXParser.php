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

/**
 * The abstract SAX parser class.
 *
 * This class represents a SAX parser. It is a abstract calss that must be
 * implemented by the real parser that must extend this class
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @package   phing.parser
 */
abstract class AbstractSAXParser
{
    /**
     * @var AbstractHandler
     */
    protected $handler;

    /**
     * Sets options for PHP interal parser. Must be implemented by the parser
     * class if it should be used.
     *
     * @param int   $opt
     * @param mixed $val
     *
     * @return bool
     */
    abstract public function parserSetOption(int $opt, $val): bool;

    /**
     * Sets the current element handler object for this parser. Usually this
     * is an object using extending "AbstractHandler".
     *
     * @param AbstractHandler $obj The handler object.
     *
     * @return void
     */
    public function setHandler(AbstractHandler $obj): void
    {
        $this->handler = $obj;
    }

    /**
     * Method that gets invoked when the parser runs over a XML start element.
     *
     * This method is called by PHP's internal parser functions and registered
     * in the actual parser implementation.
     * It gives control to the current active handler object by calling the
     * <code>startElement()</code> method.
     *
     * @param object $parser  the php's internal parser handle
     * @param string $name    the open tag name
     * @param array  $attribs the tag's attributes if any
     *
     * @return void
     *
     * @throws Exception - Exceptions may be thrown by the Handler
     */
    public function startElement($parser, string $name, array $attribs): void
    {
        $this->handler->startElement($name, $attribs);
    }

    /**
     * Method that gets invoked when the parser runs over a XML close element.
     *
     * This method is called by PHP's internal parser funcitons and registered
     * in the actual parser implementation.
     *
     * It gives control to the current active handler object by calling the
     * <code>endElement()</code> method.
     *
     * @param object $parser the php's internal parser handle
     * @param string $name   the closing tag name
     *
     * @return void
     *
     * @throws Exception - Exceptions may be thrown by the Handler
     */
    public function endElement($parser, string $name): void
    {
        $this->handler->endElement($name);
    }

    /**
     * Method that gets invoked when the parser runs over CDATA.
     *
     * This method is called by PHP's internal parser functions and registered
     * in the actual parser implementation.
     *
     * It gives control to the current active handler object by calling the
     * <code>characters()</code> method. That processes the given CDATA.
     *
     * @param resource $parser php's internal parser handle.
     * @param string   $data   the CDATA
     *
     * @return void
     *
     * @throws Exception - Exceptions may be thrown by the Handler
     */
    public function characters($parser, string $data): void
    {
        $this->handler->characters($data);
    }

    /**
     * Entrypoint for parser. This method needs to be implemented by the
     * child classt that utilizes the concrete parser
     *
     * @return int
     */
    abstract public function parse(): int;
}
