<?php

namespace Phing\Test\Task\Ext\PhpUnit;

use DOMDocument;
use DOMXPath;
use Phing\Task\Ext\PhpUnit\PHPUnitReportTask;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PHPUnitReportTaskTest extends TestCase
{
    /**
     * @dataProvider reportDocuments
     */
    public function testNormalizeReportWithWhitespaceAndMultipleContainers(string $xml): void
    {
        $document = new DOMDocument();
        $document->loadXML($xml);

        $task = new class extends PHPUnitReportTask {
            public function normalize(DOMDocument $document): void
            {
                $this->fixDocument($document);
            }
        };
        $task->normalize($document);

        $xpath = new DOMXPath($document);
        $this->assertSame(2, $xpath->query('/testsuites/testsuite[@package="Example"]')->length);
        $this->assertSame(2, $xpath->query('/testsuites/testsuite/testcase')->length);
        $this->assertSame(0, $xpath->query('/testsuites/testsuite/testsuite')->length);
    }

    public static function reportDocuments(): array
    {
        $suite = '<testsuite name="ExampleTest" namespace="Example"><testcase name="example"/></testsuite>';
        $documents = [];
        foreach ([false, true] as $nested) {
            $container = $nested ? "<testsuite>\n{$suite}\n</testsuite>" : $suite;
            $xml = "<testsuites>\n<testsuite>\n{$container}\n</testsuite>\n"
                . "<testsuite>\n{$container}\n</testsuite>\n</testsuites>";
            $documents[($nested ? 'nested' : 'shallow') . ' formatted'] = [$xml];
            $documents[($nested ? 'nested' : 'shallow') . ' compact'] = [str_replace("\n", '', $xml)];
            $documents[($nested ? 'nested' : 'shallow') . ' leading comment'] = ['<!-- report -->' . $xml];
        }

        return $documents;
    }
}
