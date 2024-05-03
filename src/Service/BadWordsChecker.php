<?php

namespace App\Service;

use PHPUnit\Framework\TestCase;

class PhpBadWordsTest extends TestCase {

    public function testCheckForBadWord() {
        $obj = new PhpBadWords();
        $obj->setDictionaryFromFile(__DIR__ . "/listeMots.php");
        $obj->setText("This is a sentence containing a badword");
        $this->assertTrue($obj->check());
    }
}