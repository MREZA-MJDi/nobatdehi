@php

    $blogPosts = [

        [
            'title' =>
                'چطور سالن مناسب خودمان را پیدا کنیم؟',

            'category' =>
                'راهنمای زیبایی',

            'image' =>
                'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=900&q=85',

            'url' => '#',
        ],

        [
            'title' =>
                'ترندهای جدید مو که ارزش امتحان کردن دارند',

            'category' =>
                'مو و استایل',

            'image' =>
                'https://images.unsplash.com/photo-1562322140-8baeececf3df?auto=format&fit=crop&w=900&q=85',

            'url' => '#',
        ],

        [
            'title' =>
                'قبل از رزرو نوبت به این نکات توجه کن',

            'category' =>
                'راهنمای NOBAT',

            'image' =>
                'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=900&q=85',

            'url' => '#',
        ],

    ];

@endphp


<section class="discovery-section">

    <div class="discovery-container">

        <div class="discovery-section-header">

            <div class="discovery-section-heading">

                <span class="discovery-section-eyebrow">
                    NOBAT MAGAZINE
                </span>

                <h2 class="discovery-section-title">
                    از مجله NOBAT
                </h2>

                <p class="discovery-section-description">
                    راهنما، ترند و نکات کاربردی دنیای زیبایی.
                </p>

            </div>

        </div>


        <div class="discovery-blog-grid">

            @foreach(
                $blogPosts
                as $post
            )

                <a
                    href="{{ $post['url'] }}"
                    class="discovery-blog-card"
                >

                    <div class="discovery-blog-image">

                        <img
                            src="{{ $post['image'] }}"
                            alt="{{ $post['title'] }}"
                            loading="lazy"
                        >

                    </div>


                    <div class="discovery-blog-body">

                        <div class="discovery-blog-meta">
                            {{ $post['category'] }}
                        </div>

                        <h3 class="discovery-blog-title">
                            {{ $post['title'] }}
                        </h3>

                        <span class="discovery-blog-link">
                            مطالعه مقاله
                            ←
                        </span>

                    </div>

                </a>

            @endforeach

        </div>

    </div>

</section>
