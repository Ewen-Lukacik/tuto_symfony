<?php

namespace App\DTO;

class CategorywithCountDTO
{

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $count
    )
    {
        
    }
}