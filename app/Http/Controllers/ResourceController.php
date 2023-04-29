<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResourceText;
use App\Models\ResourceFile;
use App\Models\ResourceURL;


use App\Models\Category;
use Illuminate\Support\Carbon;
use \Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    public function index($id)
    {
        return view('rescource.rescource_client', compact('id'));
    }

    public function RescourceDatos($id)
    {
        // Obtener el nombre del curso
        $course = DB::table('courses')->select('name')->where('id', $id)->first();

        // Obtener todas las categorías del curso seleccionado
        $categories = DB::table('categories')->select('id', 'name')->where('course_id', $id)->get();

        // Crear el objeto JSON combinando todos los resultados
        $json = [
            'course_name' => $course->name,
            'categories' => [],
        ];

        foreach ($categories as $category) {
            $category_json = [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'resources' => [],
            ];

            // Obtener los recursos de texto de la categoría
            $resources_texts = DB::table('resource_texts')
                ->select('name as resource_name', 'description as resource_description')
                ->where('category_id', $category->id)
                ->get();

            foreach ($resources_texts as $resource) {
                $resource_json = [
                    'resource_type' => 'text',
                    'resource_name' => $resource->resource_name,
                    'resource_description' => $resource->resource_description,
                ];
                $category_json['resources'][] = $resource_json;
            }

            // Obtener los recursos de URL de la categoría
            $resources_urls = DB::table('resource_urls')
                ->select('name as resource_name', 'location as resource_location')
                ->where('category_id', $category->id)
                ->get();

            foreach ($resources_urls as $resource) {
                $resource_json = [
                    'resource_type' => 'url',
                    'resource_name' => $resource->resource_name,
                    'resource_location' => $resource->resource_location,
                ];
                $category_json['resources'][] = $resource_json;
            }

            // Obtener los recursos de archivo de la categoría
            $resources_files = DB::table('resource_files')
                ->select('name as resource_name', 'location as resource_location')
                ->where('category_id', $category->id)
                ->get();

            foreach ($resources_files as $resource) {
                $resource_json = [
                    'resource_type' => 'file',
                    'resource_name' => $resource->resource_name,
                    'resource_location' => $resource->resource_location,
                ];
                $category_json['resources'][] = $resource_json;
            }

            $json['categories'][] = $category_json;
        }

        // Devolver el objeto JSON
        return response()->json($json);
    }


    public function index_admin()
    {
        return view('rescource.rescource_admin');
    }

    public function index_admin_datos()
    {
        // Obtener los recursos de tipo texto
        $recursos_texto = DB::table('resource_texts')
            ->select('id', 'name', 'description', 'category_id', DB::raw("'text' as type"))
            ->orderBy('updated_at', 'asc');
    
        // Obtener los recursos de tipo archivo
        $recursos_archivo = DB::table('resource_files')
            ->select('id', 'name', 'location', 'category_id', DB::raw("'file' as type"))
            ->orderBy('updated_at', 'asc');
    
        // Obtener los recursos de tipo URL
        $recursos_url = DB::table('resource_urls')
            ->select('id', 'name', 'location', 'category_id', DB::raw("'url' as type"))
            ->orderBy('updated_at', 'asc');
    
        // Combinar todos los resultados en una sola colección paginada
        $recursos = $recursos_texto->union($recursos_archivo)->union($recursos_url)->paginate(8);
    
        // Agregar el campo 'type' a cada objeto del resultado
        $recursos_con_tipo = $recursos->map(function ($recurso) {
            $recurso->type = strtolower($recurso->type);
            return $recurso;
        });
    
        // Devolver los resultados como JSON con el campo 'type' agregado
        return response()->json($recursos_con_tipo);
    }
    

    public function allCategories()
    {
        return Category::all();
    }

    public function createRescourceText(Request $request)
    {

        $resourceText = new ResourceText;
        $resourceText->name = $request->name;
        $resourceText->description = $request->description;
        $resourceText->category_id = $request->selectedCategory;
        $resourceText->hidden = NULL;
        $resourceText->created_at = now();
        $resourceText->save();

        return response()->json(['message' => 'Recurso creado'], 200);
    }

    public function createRescourceURL(Request $request)
    {
        $resourceURL = new ResourceURL;
        $resourceURL->name = $request->name;
        $resourceURL->location = $request->locate;
        $resourceURL->category_id = $request->selectedCategory;
        $resourceURL->hidden = NULL;
        $resourceURL->created_at = now();
        $resourceURL->save();

        return response()->json(['message' => 'Recurso creado'], 200);
    }

    public function createRescourceFile(Request $request)
    {
        $resourceFile = new ResourceURL;
        $resourceFile->name = $request->name;
        $resourceFile->location = $request->locate;
        $resourceFile->category_id = $request->selectedCategory;
        $resourceFile->hidden = NULL;
        $resourceFile->created_at = now();
        $resourceFile->save();
    }

    public function deleteResource($type, $id)
    {
        switch ($type) {
            case 'text':
                $resource = ResourceText::findOrFail($id);
                break;
            case 'url':
                $resource = ResourceURL::findOrFail($id);
                break;
            case 'file':
                $resource = ResourceFile::findOrFail($id);
                break;
            default:
                return abort(404);
        }

        $resource->delete();

        return response()->json(['success' => true]);
    }
}
