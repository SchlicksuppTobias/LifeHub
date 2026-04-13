<?php

namespace Tobias\LifeHub\shared\interfaces;

interface NutritionRepositoryInterface
{
    public function getAll(): array;

    public function search(string $term): array;
}