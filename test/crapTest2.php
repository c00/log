<?php

namespace test;
use PHPUnit\Framework\TestCase;

class crapTest2 extends TestCase
{

    public function testTruth(){
        $i1 = 1;
        $s1 = "1";
        $b1 = true;

        $i0 = 0;
        $s0 = "0";
        $b0 = false;

        $this->assertTrue((bool)$i1);
        $this->assertTrue((bool)$s1);
        $this->assertTrue((bool)$b1);

        $this->assertFalse((bool)$i0);
        $this->assertFalse((bool)$s0);
        $this->assertFalse((bool)$b0);
    }


}