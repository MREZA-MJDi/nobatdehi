<?php

namespace App\Http\Controllers\Salon;

use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\PostRequest;
use App\Models\Post;
use App\Models\Salon;
use App\Services\PostMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $salon = $this->currentSalon(
            $request
        );

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
        $salon = $this->currentSalon(
            $request
        );

        $barbers = $salon
            ->barbers()
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get();

        $services = $salon
            ->services()
            ->where(
                'is_active',
                true
            )
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
        PostRequest $request,
        PostMediaService $mediaService
    ): RedirectResponse {
        $salon = $this->currentSalon(
            $request
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

        $mediaPath = null;
        $thumbnailPath = null;

        $mediaType = $mediaService->detectType(
            $request->file('media'),
            $data['type'] ?? null
        );

        try {

            DB::transaction(
                function () use (
                    $request,
                    $salon,
                    $data,
                    $mediaService,
                    $mediaType,
                    &$mediaPath,
                    &$thumbnailPath
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Main media
                    |--------------------------------------------------------------------------
                    */

                    $mediaPath =
                        $mediaService->store(
                            $request->file('media'),
                            $salon
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Thumbnail only for video / reel
                    |--------------------------------------------------------------------------
                    */

                    if (
                        in_array(
                            $mediaType,
                            [
                                PostType::VIDEO,
                                PostType::REEL,
                            ],
                            true
                        )
                        &&
                        $request->hasFile(
                            'thumbnail'
                        )
                    ) {

                        $thumbnailPath =
                            $mediaService->storeThumbnail(
                                $request->file('thumbnail'),
                                $salon
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Create post
                    |--------------------------------------------------------------------------
                    */

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
                            $data['service_id']
                            ?? null,

                        'type' =>
                            $mediaType->value,

                        'media_path' =>
                            $mediaPath,

                        'thumbnail_path' =>
                            $thumbnailPath,

                        'title' =>
                            $data['title']
                            ?? null,

                        'caption' =>
                            $data['caption']
                            ?? null,

                        'is_active' =>
                            array_key_exists(
                                'is_active',
                                $data
                            )
                                ? (bool) $data['is_active']
                                : true,

                        'sort_order' =>
                            $data['sort_order']
                            ?? 0,
                    ]);
                }
            );

        } catch (\Throwable $e) {

            if ($mediaPath) {
                $mediaService->delete(
                    $mediaPath
                );
            }

            if ($thumbnailPath) {
                $mediaService->delete(
                    $thumbnailPath
                );
            }

            throw $e;
        }

        return redirect()
            ->route(
                'salon.posts.index'
            )
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
        $salon = $this->currentSalon(
            $request
        );

        $this->ensurePostBelongsToSalon(
            $post,
            $salon
        );

        $barbers = $salon
            ->barbers()
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get();

        $services = $salon
            ->services()
            ->where(
                'is_active',
                true
            )
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
        Post $post,
        PostMediaService $mediaService
    ): RedirectResponse {
        $salon = $this->currentSalon(
            $request
        );

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

        $oldMediaPath =
            $post->media_path;

        $oldThumbnailPath =
            $post->thumbnail_path;

        $newMediaPath = null;
        $newThumbnailPath = null;

        $currentType =
            $post->type instanceof PostType
                ? $post->type
                : PostType::tryFrom(
                (string) $post->type
            );

        if (!$currentType) {
            $currentType =
                PostType::PHOTO;
        }

        $finalType = $currentType;

        try {

            DB::transaction(
                function () use (
                    $request,
                    $post,
                    $salon,
                    $data,
                    $mediaService,
                    &$newMediaPath,
                    &$newThumbnailPath,
                    &$finalType
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | New media
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $request->hasFile(
                            'media'
                        )
                    ) {

                        $finalType =
                            $mediaService->detectType(
                                $request->file('media'),
                                $data['type'] ?? null
                            );

                        $newMediaPath =
                            $mediaService->store(
                                $request->file('media'),
                                $salon
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Existing type
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$request->hasFile(
                            'media'
                        )
                    ) {

                        $finalType =
                            $post->type instanceof PostType
                                ? $post->type
                                : PostType::tryFrom(
                                (string) $post->type
                            );

                        if (!$finalType) {
                            $finalType =
                                PostType::PHOTO;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Thumbnail
                    |--------------------------------------------------------------------------
                    */

                    if (
                        in_array(
                            $finalType,
                            [
                                PostType::VIDEO,
                                PostType::REEL,
                            ],
                            true
                        )
                    ) {

                        if (
                            $request->hasFile(
                                'thumbnail'
                            )
                        ) {

                            $newThumbnailPath =
                                $mediaService->storeThumbnail(
                                    $request->file('thumbnail'),
                                    $salon
                                );
                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | For image / GIF there is no thumbnail
                    |--------------------------------------------------------------------------
                    */

                    $thumbnailForPost =
                        in_array(
                            $finalType,
                            [
                                PostType::PHOTO,
                                PostType::GIF,
                            ],
                            true
                        )
                            ? null
                            : (
                        $newThumbnailPath
                            ?: $post->thumbnail_path
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Update
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
                            $data['service_id']
                            ?? null,

                        'type' =>
                            $finalType->value,

                        'media_path' =>
                            $newMediaPath
                                ?: $post->media_path,

                        'thumbnail_path' =>
                            $thumbnailForPost,

                        'title' =>
                            $data['title']
                            ?? null,

                        'caption' =>
                            $data['caption']
                            ?? null,

                        'is_active' =>
                            array_key_exists(
                                'is_active',
                                $data
                            )
                                ? (bool) $data['is_active']
                                : false,

                        'sort_order' =>
                            $data['sort_order']
                            ?? 0,
                    ]);
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Delete old files only after successful DB update
            |--------------------------------------------------------------------------
            */

            if (
                $newMediaPath
                && $oldMediaPath
                && $oldMediaPath !== $newMediaPath
            ) {
                $mediaService->delete(
                    $oldMediaPath
                );
            }

            if (
                $finalType === PostType::PHOTO
                || $finalType === PostType::GIF
            ) {

                if ($oldThumbnailPath) {
                    $mediaService->delete(
                        $oldThumbnailPath
                    );
                }

            } elseif (
                $newThumbnailPath
                && $oldThumbnailPath
                && $oldThumbnailPath !== $newThumbnailPath
            ) {

                $mediaService->delete(
                    $oldThumbnailPath
                );
            }

        } catch (\Throwable $e) {

            if ($newMediaPath) {
                $mediaService->delete(
                    $newMediaPath
                );
            }

            if ($newThumbnailPath) {
                $mediaService->delete(
                    $newThumbnailPath
                );
            }

            throw $e;
        }

        return redirect()
            ->route(
                'salon.posts.index'
            )
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
        $salon = $this->currentSalon(
            $request
        );

        $this->ensurePostBelongsToSalon(
            $post,
            $salon
        );

        $post->update([
            'is_active' =>
                !$post->is_active,
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
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Post $post,
        PostMediaService $mediaService
    ): RedirectResponse {
        $salon = $this->currentSalon(
            $request
        );

        $this->ensurePostBelongsToSalon(
            $post,
            $salon
        );

        $mediaService->delete(
            $post->media_path
        );

        $mediaService->delete(
            $post->thumbnail_path
        );

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
            ->where(
                'is_active',
                true
            )
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
        $isOwner =
            (bool) (
                $data['performed_by_owner']
                ?? false
            );

        if ($isOwner) {

            return;
        }

        if (
            empty(
            $data['barber_id']
            )
        ) {

            abort(
                422,
                'آرایشگر انجام‌دهنده را انتخاب کنید.'
            );
        }

        $exists = $salon
            ->barbers()
            ->whereKey(
                $data['barber_id']
            )
            ->where(
                'is_active',
                true
            )
            ->exists();

        abort_unless(
            $exists,
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
            ->whereKey(
                $serviceId
            )
            ->where(
                'is_active',
                true
            )
            ->exists();

        abort_unless(
            $exists,
            422,
            'خدمت انتخاب‌شده متعلق به این سالن نیست.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
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
