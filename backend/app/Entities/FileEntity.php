<?php

namespace App\Entities;

class FileEntity
{
    public int     $id;
    public string  $uuid;
    public ?int    $user_id;
    public string  $type;
    public string  $original_name;
    public string  $stored_name;
    public string  $path;
    public string  $url;
    public ?string $mime_type;
    public ?int    $size;
    public bool    $is_public;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id ?? null,
            'uuid'          => $this->uuid ?? null,
            'user_id'       => $this->user_id ?? null,
            'type'          => $this->type ?? 'general',
            'original_name' => $this->original_name ?? '',
            'stored_name'   => $this->stored_name ?? '',
            'path'          => $this->path ?? '',
            'url'           => $this->url ?? '',
            'mime_type'     => $this->mime_type ?? null,
            'size'          => $this->size ?? null,
            'is_public'     => $this->is_public ?? true,
            'created_at'    => $this->created_at ?? null,
        ];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }
}
