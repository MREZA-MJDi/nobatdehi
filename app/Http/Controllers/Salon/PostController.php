<?php

namespace App\Http\Controllers\Salon;

use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\PostRequest;
use App\Models\Post;
use App\Models\Salon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {
        $salon = $this->currentSalon($request);

        $posts = $salon
            ->posts()
            ->with([
                'barber',
                'service',
            ])
            ->paginate(20);

        return view(
            'salon.posts.index',
            compact(
                'salon',
                'posts'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        Request $request
    ): View {
        $salon = $this->currentSalon($request);

        $barbers = $salon
            ->barbers()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $services = $salon
            ->services()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $types = PostType::cases();

        return view(
            'salon.posts.create',
            compact(
                'salon',
                'barbers',
                'services',
                'types'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        PostRequest $request
    ): RedirectResponse {
        $salon = $this->currentSalon($request);

        $data = $request->validated();

        $this->validatePerformer(
            $salon,
            $data
        );

        $this->validateService(
            $salon,
            $data['service_id'] ?? null
        );

        $mediaPath = null;
        $thumbnailPath = null;

        try {

            DB::transaction(function () use (
                $request,
                $salon,
                $data,
                &$mediaPath,
                &$thumbnailPath
            ) {

                $mediaPath = $request
                    ->file('media')
                    ->store(
                        'posts/' . $salon->id,
                        'public'
                    );

                if ($request->hasFile('thumbnail')) {

                    $thumbnailPath = $request
                        ->file('thumbnail')
                        ->store(
                            'posts/' .
                            $salon->id .
                            '/thumbnails',
                            'public'
                        );
                }

                Post::create([

                    'salon_id' =>
                        $salon->id,

                    'barber_id' =>
                        $data['performed_by_owner']
                            ? null
                            : $data['barber_id'],

                    'performed_by_owner' =>
                        (bool) $data['performed_by_owner'],

                    'service_id' =>
                        $data['service_id'] ?? null,

                    'type' =>
                        $data['type'],

                    'media_path' =>
                        $mediaPath,

                    'thumbnail_path' =>
                        $thumbnailPath,

                    'title' =>
                        $data['title'] ?? null,

                    'caption' =>
                        $data['caption'] ?? null,

                    'is_active' =>
                        $data['is_active'] ?? true,

                    'sort_order' =>
                        $data['sort_order'] ?? 0,
                ]);
            });

        } catch (\Throwable $e) {

            if ($mediaPath) {
                Storage::disk('public')->delete($mediaPath);
            }

            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            throw $e;
        }

        return redirect()
            ->route('salon.posts.index')
            ->with(
                'success',
                'پست با موفقیت ایجاد شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request,
        Post $post
    ): View {
        $salon = $this->currentSalon($request);

        $this->ensurePostBelongsToSalon(
            $post,
            $salon
        );

        $barbers = $salon
            ->barbers()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $services = $salon
            ->services()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $types = PostType::cases();

        return view(
            'salon.posts.edit',
            compact(
                'salon',
                'post',
                'barbers',
                'services',
                'types'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        PostRequest $request,
        Post $post
    ): RedirectResponse {
        $salon = $this->currentSalon($request);

        $this->ensurePostBelongsToSalon(
            $post,
            $salon
        );

        $data = $request->validated();

        $this->validatePerformer(
            $salon,
            $data
        );

        $this->validateService(
            $salon,
            $data['service_id'] ?? null
        );

        $oldMediaPath = $post->media_path;
        $oldThumbnailPath = $post->thumbnail_path;

        $newMediaPath = null;
        $newThumbnailPath = null;

        try {

            DB::transaction(function () use (
                $request,
                $post,
                $salon,
                $data,
                &$newMediaPath,
                &$newThumbnailPath,
                $oldMediaPath,
                $oldThumbnailPath
            ) {

                /*
                |--------------------------------------------------------------------------
                | New media
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('media')) {

                    $newMediaPath = $request
                        ->file('media')
                        ->store(
                            'posts/' . $salon->id,
                            'public'
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | New thumbnail
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('thumbnail')) {

                    $newThumbnailPath = $request
                        ->file('thumbnail')
                        ->store(
                            'posts/' .
                            $salon->id .
                            '/thumbnails',
                            'public'
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | Main update
                |--------------------------------------------------------------------------
                */

                $post->update([

                    'barber_id' =>
                        $data['performed_by_owner']
                            ? null
                            : $data['barber_id'],

                    'performed_by_owner' =>
                        (bool) $data['performed_by_owner'],

                    'service_id' =>
                        $data['service_id'] ?? null,

                    'type' =>
                        $data['type'],

                    'title' =>
                        $data['title'] ?? null,

                    'caption' =>
                        $data['caption'] ?? null,

                    'is_active' =>
                        $data['is_active'] ?? false,

                    'sort_order' =>
                        $data['sort_order'] ?? 0,

                    'media_path' =>
                        $newMediaPath
                            ?: $post->media_path,

                    'thumbnail_path' =>
                        $newThumbnailPath
                            ?: $post->thumbnail_path,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Replace old media
                |--------------------------------------------------------------------------
                */

                if ($newMediaPath && $oldMediaPath) {

                    Storage::disk('public')
                        ->delete($oldMediaPath);
                }


                /*
                |--------------------------------------------------------------------------
                | Replace old thumbnail
                |--------------------------------------------------------------------------
                */

                if (
                    $newThumbnailPath &&
                    $oldThumbnailPath
                ) {

                    Storage::disk('public')
                        ->delete($oldThumbnailPath);
                }


                /*
                |--------------------------------------------------------------------------
                | Photo does not need a thumbnail
                |--------------------------------------------------------------------------
                */

                if (
                    $data['type'] === PostType::PHOTO->value &&
                    $post->thumbnail_path
                ) {

                    Storage::disk('public')
                        ->delete($post->thumbnail_path);

                    $post->thumbnail_path = null;
                    $post->save();
                }
            });

        } catch (\Throwable $e) {

            if ($newMediaPath) {
                Storage::disk('public')
                    ->delete($newMediaPath);
            }

            if ($newThumbnailPath) {
                Storage::disk('public')
                    ->delete($newThumbnailPath);
            }

            throw $e;
        }

        return redirect()
            ->route('salon.posts.index')
            ->with(
                'success',
                'پست با موفقیت ویرایش شد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle
    |--------------------------------------------------------------------------
    */

    public function toggle(
        Request $request,
        Post $post
    ): RedirectResponse {
        $salon = $this->currentSalon($request);

        $this->ensurePostBelongsToSalon(
            $post,
            $salon
        );

        $post->update([
            'is_active' => !$post->is_active,
        ]);

        return back()->with(
            'success',
            $post->is_active
                ? 'پست منتشر شد.'
                : 'پست مخفی شد.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Post $post
    ): RedirectResponse {
        $salon = $this->currentSalon($request);

        $this->ensurePostBelongsToSalon(
            $post,
            $salon
        );

        $paths = [
            $post->media_path,
            $post->thumbnail_path,
        ];

        foreach ($paths as $path) {

            if (!$path) {
                continue;
            }

            Storage::disk('public')
                ->delete($path);
        }

        $post->delete();

        return back()->with(
            'success',
            'پست حذف شد.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Current salon
    |--------------------------------------------------------------------------
    */

    private function currentSalon(
        Request $request
    ): Salon {
        $user = $request->user();

        abort_unless(
            $user?->isSalonOwner(),
            403
        );

        $salon = $user
            ->managedSalons()
            ->where('is_active', true)
            ->first();

        abort_unless(
            $salon instanceof Salon,
            404
        );

        return $salon;
    }


    /*
    |--------------------------------------------------------------------------
    | Performer validation
    |--------------------------------------------------------------------------
    */

    private function validatePerformer(
        Salon $salon,
        array $data
    ): void {
        $isOwner = (bool) (
            $data['performed_by_owner'] ?? false
        );

        if ($isOwner) {

            if (!empty($data['barber_id'])) {

                abort(
                    422,
                    'وقتی انجام‌دهنده صاحب سالن است، آرایشگر نباید انتخاب شود.'
                );
            }

            return;
        }

        if (empty($data['barber_id'])) {

            abort(
                422,
                'آرایشگر انجام‌دهنده را انتخاب کنید.'
            );
        }

        $barberExists = $salon
            ->barbers()
            ->whereKey($data['barber_id'])
            ->where('is_active', true)
            ->exists();

        abort_unless(
            $barberExists,
            422,
            'آرایشگر انتخاب‌شده عضو این سالن نیست.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Service validation
    |--------------------------------------------------------------------------
    */

    private function validateService(
        Salon $salon,
        ?int $serviceId
    ): void {
        if (!$serviceId) {
            return;
        }

        $exists = $salon
            ->services()
            ->whereKey($serviceId)
            ->where('is_active', true)
            ->exists();

        abort_unless(
            $exists,
            422,
            'خدمت انتخاب‌شده متعلق به این سالن نیست.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Post ownership
    |--------------------------------------------------------------------------
    */

    private function ensurePostBelongsToSalon(
        Post $post,
        Salon $salon
    ): void {
        abort_unless(
            (int) $post->salon_id ===
            (int) $salon->id,
            404
        );
    }
}
