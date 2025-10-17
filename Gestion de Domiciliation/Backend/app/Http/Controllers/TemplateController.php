<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
    /**
     * Afficher la liste des templates.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Template::with('creator');

        // Filtrer par type de document si spécifié
        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // Filtrer par type de template si spécifié
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filtrer par statut actif/inactif
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Recherche par nom ou description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $templates = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $templates,
            'document_types' => Template::DOCUMENT_TYPES,
            'template_types' => Template::TYPES,
        ]);
    }

    /**
     * Enregistrer un nouveau template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'type' => ['required', Rule::in(Template::TYPES)],
            'document_type' => ['required', Rule::in(Template::DOCUMENT_TYPES)],
            'variables' => 'required|json',
            'version' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Générer un nom de fichier unique
        $fileName = Str::slug($request->name) . '_' . time() . '.' . $this->getExtensionByType($request->type);

        // Stocker le contenu du template
        $path = 'templates/' . $fileName;
        Storage::put($path, $request->content);

        // Convertir les variables JSON en tableau PHP pour le stockage
        $variables = json_decode($validated['variables'], true);

        // Créer le template
        $template = Template::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'path' => $path,
            'type' => $validated['type'],
            'document_type' => $validated['document_type'],
            'variables' => $variables,
            'version' => $validated['version'],
            'created_by' => Auth::id(),
            'is_active' => $request->input('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template créé avec succès',
            'data' => $template->load('creator')
        ], 201);
    }

    /**
     * Afficher les détails d'un template spécifique.
     *
     * @param  \App\Models\Template  $template
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Template $template)
    {
        return response()->json([
            'success' => true,
            'data' => $template->load('creator')
        ]);
    }

    /**
     * Mettre à jour un template existant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Template  $template
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'type' => ['required', Rule::in(Template::TYPES)],
            'document_type' => ['required', Rule::in(Template::DOCUMENT_TYPES)],
            'variables' => 'required|json',
            'version' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Vérifier si le contenu a changé
        if ($template->content !== $request->content || $template->type !== $request->type) {
            // Supprimer l'ancien fichier si nécessaire
            if (Storage::exists($template->path)) {
                Storage::delete($template->path);
            }

            // Générer un nouveau nom de fichier
            $fileName = Str::slug($request->name) . '_' . time() . '.' . $this->getExtensionByType($request->type);
            $path = 'templates/' . $fileName;

            // Stocker le nouveau contenu
            Storage::put($path, $request->content);
            $template->path = $path;
        }

        // Convertir les variables JSON en tableau PHP pour le stockage
        $variables = json_decode($validated['variables'], true);

        // Mettre à jour le template
        $template->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'document_type' => $validated['document_type'],
            'variables' => $variables,
            'version' => $validated['version'],
            'is_active' => $request->input('is_active', $template->is_active),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template mis à jour avec succès',
            'data' => $template->fresh()->load('creator')
        ]);
    }

    /**
     * Supprimer un template spécifique.
     *
     * @param  \App\Models\Template  $template
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Template $template)
    {
        // Supprimer le fichier associé
        if (Storage::exists($template->path)) {
            Storage::delete($template->path);
        }

        // Supprimer le template
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template supprimé avec succès'
        ]);
    }

    /**
     * Obtenir les types de documents disponibles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocumentTypes()
    {
        return response()->json([
            'success' => true,
            'data' => Template::DOCUMENT_TYPES
        ]);
    }

    /**
     * Obtenir les types de templates disponibles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplateTypes()
    {
        return response()->json([
            'success' => true,
            'data' => Template::TYPES
        ]);
    }

    /**
     * Activer ou désactiver un template
     *
     * @param  \App\Models\Template  $template
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Template $template, Request $request)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $template->update([
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->is_active
                ? 'Template activé avec succès'
                : 'Template désactivé avec succès',
            'data' => $template->fresh()
        ]);
    }

    /**
     * Obtenir l'extension de fichier en fonction du type de template.
     *
     * @param string $type
     * @return string
     */
    private function getExtensionByType(string $type): string
    {
        return match ($type) {
            'blade' => 'blade.php',
            'word' => 'docx',
            'pdf' => 'pdf',
            'html' => 'html',
            default => 'txt',
        };
    }
}
