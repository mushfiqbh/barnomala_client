<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function __construct(private readonly ImageService $imageService)
    {
    }

    public function index(Request $request)
    {
        $tab = $request->string('tab')->toString();
        $query = Post::with('artifacts')->latest('published_at')->latest('id');

        if ($tab !== '' && array_key_exists($tab, Post::POST_TYPES)) {
            $query->where('type', $tab);
        }

        return view('admin.posts.index', [
            'posts' => $query->paginate(15)->withQueryString(),
            'tab' => $tab ?: 'all',
            'types' => $this->types(),
            'postTypes' => Post::POST_TYPES,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.posts.form', [
            'post' => new Post([
                'type' => $request->string('type')->toString() ?: Post::NOTICE,
                'is_active' => true,
                'published_at' => now()->toDateString(),
            ]),
            'types' => $this->types(),
            'isEditing' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePost($request);
        $validated['source_type'] = $this->sourceType($validated['type']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_urgent'] = $request->boolean('is_urgent');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $post = Post::create($validated);
        $this->storeFiles($request, $post);

        return redirect()->route('admin.posts.index', ['tab' => $this->tabFor($post)])
            ->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        $post->load('artifacts');

        return view('admin.posts.form', [
            'post' => $post,
            'types' => $this->types(),
            'isEditing' => true,
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $validated = $this->validatePost($request);
        $validated['source_type'] = $this->sourceType($validated['type']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_urgent'] = $request->boolean('is_urgent');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            if (isset($post->image_json['path'])) {
                Storage::disk('public')->delete($post->image_json['path']);
            }

            $path = $this->imageService->convertToWebp($request->file('image'), 'news/images');
            $validated['image_json'] = ['url' => Storage::url($path), 'path' => $path];
        }

        $post->update($validated);
        $this->deleteArtifacts($request, $post);
        $this->storeFiles($request, $post);

        return redirect()->route('admin.posts.index', ['tab' => $this->tabFor($post)])
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        if (isset($post->image_json['path'])) {
            Storage::disk('public')->delete($post->image_json['path']);
        }

        foreach ($post->artifacts as $artifact) {
            Storage::disk('public')->delete($artifact->file_path);
        }

        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted successfully.');
    }

    private function validatePost(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys($this->types()))],
            'title' => ['required', 'string', 'max:255'],
            'content' => [Rule::requiredIf(in_array($request->input('type'), [Post::NOTICE, Post::NEWS], true)), 'nullable', 'string'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'class_label' => ['nullable', 'string', 'max:255'],
            'published_at' => ['required', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_urgent' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'artifacts' => ['nullable', 'array'],
            'artifacts.*' => ['file', 'max:20480'],
            'delete_artifacts' => ['nullable', 'array'],
        ]);

        unset($validated['image'], $validated['artifacts'], $validated['delete_artifacts']);

        return $validated;
    }

    private function storeFiles(Request $request, Post $post): void
    {
        if (!$request->hasFile('artifacts')) {
            return;
        }

        $directory = match ($post->source_type) {
            Post::NOTICE => 'notices/artifacts',
            Post::NEWS => 'news/artifacts',
            default => 'downloads',
        };

        foreach ($request->file('artifacts') as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $path = $file->store($directory, 'public');
            $post->artifacts()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    private function deleteArtifacts(Request $request, Post $post): void
    {
        foreach ((array) $request->input('delete_artifacts', []) as $artifactId => $shouldDelete) {
            if (!$shouldDelete) {
                continue;
            }

            $artifact = $post->artifacts()->find($artifactId);
            if ($artifact) {
                Storage::disk('public')->delete($artifact->file_path);
                $artifact->delete();
            }
        }
    }

    private function types(): array
    {
        return Post::POST_TYPES;
    }

    private function sourceType(string $type): string
    {
        return match ($type) {
            Post::NOTICE => Post::NOTICE,
            Post::NEWS => Post::NEWS,
            default => 'download',
        };
    }

    private function tabFor(Post $post): string
    {
        if (in_array($post->source_type, [Post::NOTICE, Post::NEWS], true)) {
            return $post->source_type;
        }

        return 'downloads';
    }
}
