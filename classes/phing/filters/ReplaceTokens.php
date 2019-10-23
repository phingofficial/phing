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

/**
 * Replaces tokens in the original input with user-supplied values.
 *
 * Example:
 *
 * <pre><replacetokens begintoken="#" endtoken="#">;
 *   <token key="DATE" value="${TODAY}"/>
 * </replacetokens></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.ReplaceTokens">
 *   <param type="tokenchar" name="begintoken" value="#"/>
 *   <param type="tokenchar" name="endtoken" value="#"/>
 *   <param type="token" name="DATE" value="${TODAY}"/>
 * </filterreader></pre>
 *
 * @author  <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author  hans lellelid, hans@velum.net
 * @see     BaseParamFilterReader
 * @package phing.filters
 */
class ReplaceTokens extends BaseParamFilterReader implements ChainableReader
{

    /**
     * Default "begin token" character.
     *
     * @var string
     */
    const DEFAULT_BEGIN_TOKEN = "@";

    /**
     * Default "end token" character.
     *
     * @var string
     */
    const DEFAULT_END_TOKEN = "@";

    /**
     * Array to hold the replacee-replacer pairs (String to String).
     *
     * @var array
     */
    private $tokens = [];

    /**
     * Array to hold the token sources that make tokens from
     * different sources available
     *
     * @var array
     */
    private $tokensources = [];

    /**
     * Array holding all tokens given directly to the Filter and
     * those passed via a TokenSource.
     *
     * @var array|null
     */
    private $alltokens = null;

    /**
     * Character marking the beginning of a token.
     *
     * @var string
     */
    private $beginToken = "@"; // self::DEFAULT_BEGIN_TOKEN;

    /**
     * Character marking the end of a token.
     *
     * @var string
     */
    private $endToken = "@"; //self::DEFAULT_END_TOKEN;

    /**
     * Performs lookup on key and returns appropriate replacement string.
     *
     * @param  array $matches Array of 1 el containing key to search for.
     * @return string Text with which to replace key or value of key if none is found.
     */
    private function replaceTokenCallback($matches)
    {
        $key = $matches[1];

        /* Get tokens from tokensource and merge them with the
         * tokens given directly via build file. This should be
         * done a bit more elegantly
         */
        if ($this->alltokens === null) {
            $this->alltokens = [];

            $count = count($this->tokensources);
            for ($i = 0; $i < $count; $i++) {
                $source = $this->tokensources[$i];
                $this->alltokens = array_merge($this->alltokens, $source->getTokens());
            }

            $this->alltokens = array_merge($this->tokens, $this->alltokens);
        }

        $tokens = $this->alltokens;

        $replaceWith = null;
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i]->getKey() === $key) {
                $replaceWith = $tokens[$i]->getValue();
            }
        }

        if ($replaceWith === null) {
            $replaceWith = $this->beginToken . $key . $this->endToken;
            $this->log("No token defined for key \"" . $this->beginToken . $key . $this->endToken . "\"");
        } else {
            $this->log(
                "Replaced \"" . $this->beginToken . $key . $this->endToken . "\" with \"" . $replaceWith . "\"",
                Project::MSG_VERBOSE
            );
        }

        return $replaceWith;
    }

    /**
     * Returns stream with tokens having been replaced with appropriate values.
     * If a replacement value is not found for a token, the token is left in the stream.
     *
     * @param  int $len
     * @return mixed filtered stream, -1 on EOF.
     */
    public function read($len = null)
    {
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        // read from next filter up the chain
        $buffer = $this->in->read($len);

        if ($buffer === -1) {
            return -1;
        }

        // filter buffer
        $buffer = preg_replace_callback(
            "/" . preg_quote($this->beginToken, '/') . "([\w\.\-:]+?)" . preg_quote($this->endToken, '/') . "/",
            [$this, 'replaceTokenCallback'],
            $buffer
        );

        return $buffer;
    }

    /**
     * Sets the "begin token" character.
     *
     * @param string $beginToken the character used to denote the beginning of a token.
     */
    public function setBeginToken($beginToken)
    {
        $this->beginToken = (string) $beginToken;
    }

    /**
     * Returns the "begin token" character.
     *
     * @return string The character used to denote the beginning of a token.
     */
    public function getBeginToken()
    {
        return $this->beginToken;
    }

    /**
     * Sets the "end token" character.
     *
     * @param string $endToken the character used to denote the end of a token
     */
    public function setEndToken($endToken)
    {
        $this->endToken = (string) $endToken;
    }

    /**
     * Returns the "end token" character.
     *
     * @return string the character used to denote the beginning of a token
     */
    public function getEndToken()
    {
        return $this->endToken;
    }

    /**
     * Adds a token element to the map of tokens to replace.
     *
     * @return object The token added to the map of replacements.
     *                Must not be <code>null</code>.
     */
    public function createToken()
    {
        $num = array_push($this->tokens, new Token());

        return $this->tokens[$num - 1];
    }

    /**
     * Adds a token source to the sources of this filter.
     *
     * @return object A Reference to the source just added.
     */
    public function createTokensource()
    {
        $num = array_push($this->tokensources, new TokenSource());

        return $this->tokensources[$num - 1];
    }

    /**
     * Sets the map of tokens to replace.
     * ; used by ReplaceTokens::chain()
     *
     * @param    $tokens
     * @throws   Exception
     * @internal param A $array map (String->String) of token keys to replacement
     *              values. Must not be <code>null</code>.
     */
    public function setTokens($tokens)
    {
        // type check, error must never occur, bad code of it does
        if (!is_array($tokens)) {
            throw new Exception("Expected 'array', got something else");
        }

        $this->tokens = $tokens;
    }

    /**
     * Returns the map of tokens which will be replaced.
     * ; used by ReplaceTokens::chain()
     *
     * @return array A map (String->String) of token keys to replacement values.
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Sets the tokensources to use; used by ReplaceTokens::chain()
     *
     * @param    $sources
     * @throws   Exception
     * @internal param An $array array of token sources.
     */
    public function setTokensources($sources)
    {
        // type check
        if (!is_array($sources)) {
            throw new Exception("Exspected 'array', got something else");
        }
        $this->tokensources = $sources;
    }

    /**
     * Returns the token sources used by this filter; used by ReplaceTokens::chain()
     *
     * @return array
     */
    public function getTokensources()
    {
        return $this->tokensources;
    }

    /**
     * Creates a new ReplaceTokens using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader
     * @return ReplaceTokens A new filter based on this configuration, but filtering
     *                the specified reader
     * @throws Exception
     * @internal param A $object Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new ReplaceTokens($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setBeginToken($this->getBeginToken());
        $newFilter->setEndToken($this->getEndToken());
        $newFilter->setTokens($this->getTokens());
        $newFilter->setTokensources($this->getTokensources());
        $newFilter->setInitialized(true);

        return $newFilter;
    }

    /**
     * Initializes tokens and loads the replacee-replacer hashtable.
     * This method is only called when this filter is used through
     * a <filterreader> tag in build file.
     */
    private function initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            for ($i = 0, $paramsCount = count($params); $i < $paramsCount; $i++) {
                if ($params[$i] !== null) {
                    $type = $params[$i]->getType();
                    if ($type === "tokenchar") {
                        $name = $params[$i]->getName();
                        if ($name === "begintoken") {
                            $this->beginToken = substr($params[$i]->getValue(), 0, strlen($params[$i]->getValue()));
                        } else {
                            if ($name === "endtoken") {
                                $this->endToken = substr($params[$i]->getValue(), 0, strlen($params[$i]->getValue()));
                            }
                        }
                    } else {
                        if ($type === "token") {
                            $name = $params[$i]->getName();
                            $value = $params[$i]->getValue();

                            $tok = new Token();
                            $tok->setKey($name);
                            $tok->setValue($value);

                            $this->tokens[] = $tok;
                        } else {
                            if ($type === "tokensource") {
                                // Store data from nested tags in local array
                                $arr = [];

                                $subparams = $params[$i]->getParams();
                                foreach ($subparams as $subparam) {
                                    $arr[$subparam->getName()] = $subparam->getValue();
                                }

                                // Create TokenSource
                                $tokensource = new TokenSource();
                                if (isset($arr["classname"])) {
                                    $tokensource->setClassname($arr["classname"]);
                                }

                                // Copy other parameters 1:1 to freshly created TokenSource
                                foreach ($arr as $key => $value) {
                                    if (strtolower($key) === "classname") {
                                        continue;
                                    }
                                    $param = $tokensource->createParam();
                                    $param->setName($key);
                                    $param->setValue($value);
                                }

                                $this->tokensources[] = $tokensource;
                            }
                        }
                    }
                }
            }
        }
    }
}
