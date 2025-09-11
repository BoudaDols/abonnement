<?php
   use PHPUnit\Framework\TestCase;
   use App\App;

   class AppTest extends TestCase {
      public function testRun() {
         $app = new App();
         $this->assertEquals("Hello World from App!", $app->run());
      }
      
      public function testAppClassExists() {
         $this->assertTrue(class_exists(App::class));
      }
   }