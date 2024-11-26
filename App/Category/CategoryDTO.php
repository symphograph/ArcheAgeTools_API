<?php

namespace App\Category;


use Symphograph\Bicycle\DTO\DTOTrait;

class CategoryDTO
{
    use DTOTrait;
    const string tableName = 'Categories';

    public ?int    $id;
    public ?string $name;
    public ?int    $parent;
    public ?string $description;
    public bool    $visible = true;
    public ?int    $deep;
    public ?string $icon;
}