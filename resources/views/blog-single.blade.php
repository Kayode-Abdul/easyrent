@include('header')

<div class="hero-wrap ftco-degree-bg" style="background-image: url('{{ $post->cover_photo ?? '/assets/images/bg_1.jpg' }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text justify-content-center align-items-center">
            <div class="col-lg-8 col-md-6 ftco-animate d-flex align-items-end">
                <div class="text text-center">
                    <h1 class="mb-4">{{ $post->topic }}</h1>
                    <p class="meta">
                        <span><i class="icon-calendar mr-2"></i>{{ $post->date->format('M d, Y') }}</span>
                        <span><i class="icon-user mr-2"></i>{{ $post->author }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 ftco-animate">
                <div class="blog-single-content">
                    @if($post->cover_photo)
                        <div class="blog-image mb-4">
                            <img src="{{ $post->cover_photo }}" alt="{{ $post->topic }}" class="img-fluid rounded">
                        </div>
                    @endif
                    
                    <div class="blog-meta mb-4">
                        <span class="date"><i class="icon-calendar mr-2"></i>{{ $post->date->format('F d, Y') }}</span>
                        <span class="author"><i class="icon-user mr-2"></i>By {{ $post->author }}</span>
                    </div>
                    
                    <div class="blog-content">
                        {!! nl2br(e($post->content)) !!}
                    </div>
                    
                    <div class="blog-navigation mt-5">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="/blog" class="btn btn-primary">
                                    <i class="icon-arrow-left mr-2"></i>Back to Blog
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="share-buttons">
                                    <span class="mr-3">Share:</span>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" class="btn btn-sm btn-outline-primary mr-2">
                                        <i class="icon-facebook"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($post->topic) }}" target="_blank" class="btn btn-sm btn-outline-info mr-2">
                                        <i class="icon-twitter"></i>
                                    </a>
                                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->fullUrl()) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="icon-linkedin"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 sidebar ftco-animate">
                <div class="sidebar-box">
                    <h3>Related Posts</h3>
                    @if($relatedPosts->count() > 0)
                        @foreach($relatedPosts as $related)
                            <div class="block-21 mb-4 d-flex">
                                <a class="blog-img mr-4" style="background-image: url({{ $related->cover_photo ?? '/assets/images/image_1.jpg' }});"></a>
                                <div class="text">
                                    <h3 class="heading"><a href="/readmore/{{ $related->topic_url }}">{{ $related->topic }}</a></h3>
                                    <div class="meta">
                                        <div><a href="#"><span class="icon-calendar"></span> {{ $related->date->format('M d, Y') }}</a></div>
                                        <div><a href="#"><span class="icon-person"></span> {{ $related->author }}</a></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p>No related posts found.</p>
                    @endif
                </div>
                
                <div class="sidebar-box">
                    <h3>Categories</h3>
                    <ul class="categories">
                        <li><a href="#">Property Management <span>(12)</span></a></li>
                        <li><a href="#">Real Estate Tips <span>(8)</span></a></li>
                        <li><a href="#">Tenant Relations <span>(6)</span></a></li>
                        <li><a href="#">Investment <span>(4)</span></a></li>
                        <li><a href="#">Market Trends <span>(3)</span></a></li>
                    </ul>
                </div>
                
                <div class="sidebar-box">
                    <h3>Tag Cloud</h3>
                    <div class="tagcloud">
                        <a href="#" class="tag-cloud-link">property</a>
                        <a href="#" class="tag-cloud-link">management</a>
                        <a href="#" class="tag-cloud-link">rent</a>
                        <a href="#" class="tag-cloud-link">tenant</a>
                        <a href="#" class="tag-cloud-link">landlord</a>
                        <a href="#" class="tag-cloud-link">investment</a>
                        <a href="#" class="tag-cloud-link">real estate</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('footer')