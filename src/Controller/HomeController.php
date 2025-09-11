<?php
   namespace App\Controller;

   use Psr\Log\LoggerInterface;

   class HomeController {
      private LoggerInterface $logger;

      public function __construct(LoggerInterface $logger) {
         $this->logger = $logger;
      }

      public function index(): string {
         $this->logger->info("Page d'accueil visitée");
         return "Bienvenue sur la page d'accueil 🚀";
      }

      public function hello(string $name): string {
         $this->logger->info("Page hello visitée avec $name");
         return "Hello, $name ! 👋";
      }
   }