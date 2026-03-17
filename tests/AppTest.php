<?php
   use PHPUnit\Framework\TestCase;
   use App\App;

   class AppTest extends TestCase {
      public function testAppClassExists() {
         $this->assertTrue(class_exists(App::class));
      }
   }