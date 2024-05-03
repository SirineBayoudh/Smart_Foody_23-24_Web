<?php

namespace App\Service;

class PhpBadWords {
    private $dictionary;
    private $text;

    public function setDictionaryFromFile($filePath) {
        $this->dictionary = require $filePath;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function check() {
        foreach ($this->dictionary as $word) {
            if (strpos($this->text, $word) !== false) {
                return true;
            }
        }
        return false;
    }
}