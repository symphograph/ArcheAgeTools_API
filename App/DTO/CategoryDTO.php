<?php

namespace App\DTO;


use Symphograph\Bicycle\DTO\DTOTrait;

class CategoryDTO extends DTO
{
    use DTOTrait;
    const tableName = 'Categories';
    public ?int    $id;
    public ?string $name;
    public ?int    $parent;
    public ?string $description;
    public bool    $visible = true;
    public ?int    $deep;
    public ?string $icon;
}