<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1. Generamos un nombre único para el archivo físico
        $fileName = 'avatar_' . Str::uuid() . '.jpg';

        // 2. Usamos una API gratuita para generar la imagen
        // urlencode() asegura que los nombres con espacios no rompan la URL
        $name = urlencode(fake()->name());
        $url = "https://ui-avatars.com/api/?name={$name}&background=random&color=fff&size=256";

        // Después
        try {
            $imageContent = @file_get_contents($url);
            if ($imageContent === false) {
                throw new \Exception('No se pudo descargar la imagen');
            }
            Storage::disk('public')->put('userImages/' . $fileName, $imageContent);
        } catch (\Exception $e) {
            // Crear una imagen placeholder de 1x1 pixel transparente (PNG mínimo válido)
            $placeholder = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
            );
            Storage::disk('public')->put('userImages/' . $fileName, $placeholder);
        }

        // 5. Retornamos solo la URL relativa para la base de datos
        return [
            'url' => 'userImages/' . $fileName,
            // imageable_id y imageable_type se asignarán automáticamente desde el Seeder
        ];
    }
}
