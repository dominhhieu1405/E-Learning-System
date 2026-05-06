<!-- Sidebar Wrapper -->
<div id="sidebar-container" itemscope="itemscope" itemtype="https://schema.org/WPSideBar" role="banner" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">

    <div class="theiaStickySidebar" style="padding-top: 0px; padding-bottom: 1px; position: static; transform: none;">
        <div class="sidebar section" id="sidebar">
            <div class="widget">
                <div class="widget-content">
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5588133464975020"
                            crossorigin="anonymous"></script>
                    <!-- Vuong-1 -->
                    <ins class="adsbygoogle"
                            style="display:block"
                            data-ad-client="ca-pub-5588133464975020"
                            data-ad-slot="3759424216"
                            data-ad-format="auto"
                            data-full-width-responsive="true"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </div>

            <div class="widget LinkList">
                <div class="widget-title">
                    <h3 class="title">
                        {{ __('layout.sidebar_follow_us') }}
                    </h3>
                </div>
                <div class="widget-content">
                    <ul class="socialFilter social-bg-hover social">
                        <li>
                            <a class="facebook" href="https://www.facebook.com/study.with.me.2k6" rel="noopener noreferrer" target="_blank" title="facebook"> Facebook </a>
                        </li>
                        <li>
                            <a class="whatsapp" href="#" rel="noopener noreferrer" target="_blank" title="whatsapp"> Whatsapp </a>
                        </li>
                        <li>
                            <a class="instagram" href="#" rel="noopener noreferrer" target="_blank" title="instagram"> Instagram </a>
                        </li>
                        <li>
                            <a class="youtube" href="#" rel="noopener noreferrer" target="_blank" title="youtube"> Youtube </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="widget PopularPosts">
                <div class="widget-title">
                    <h3 class="title">
                        {{ __('layout.sidebar_hot_courses') }}
                    </h3>
                </div>
                <div class="widget-content sidebar-posts">


                    @foreach(\Models\Web::courses(1, 5, "views") as $i => $course)
                        <div class="popular-post post item{{$i}}">
                            <div class="relatedui-posts-data">
                                <span class="post-author">{{ \Models\Web::typeData($course->type)->name }}</span>
                                <span class="label-news-flex">{{ \Models\Web::subjectData($course->subject)->name }}</span>
                            </div>
                            <div class="relatedui-posts-box">
                                <div class="relatedui-posts-box-flex">
                                    <h3 class="entry-title vcard">
                                        <a href="{{url("course", ["id" => $course->id])}}" rel="bookmark" title="{{ $course->name }}">{{ $course->name }}</a>
                                    </h3>
                                    <div class="post-snip flex">
                                        <span class="post-date" style="margin: 0">{{ date("d/m/y H:i", strtotime($course->time_add))  }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
            <div class="widget HTML">
                <div class="widget-title">
                    <h3 class="title">
                        {{ __('layout.sidebar_most_viewed_docs') }}
                    </h3>
                </div>
                <div class="widget-content">

                    <div class="BiggerSidebarOk">

                        @foreach(\Models\Web::documents(1, 5, "views") as $document)
                            <div class="sidebarui-posts">
                                <div class="relatedui-posts-box">
                                    <h3 class="entry-title" style="margin: 0.5em 0">
                                        <a href="{{url("document", ["id" => $document->id])}}" title="{{ $document->name }}">
                                            {{ $document->name }}
                                        </a>
                                    </h3>
                                    <div class="post-snip">
                                        <span class="post-date" style="margin: 0">{{ date("d/m/y H:i", strtotime($document->time_add))  }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach


                    </div>

                </div>
            </div>


            <div class="widget HTML" style="display: none">
                <div class="widget-title">
                    <h3 class="title">
                        Comments
                    </h3>
                </div>
                <div class="widget-content">
                    <div class="comment-list">
                        <div class="cmmTool">
                            <a class="engine-link" href="#">
                                            <span class="comment-image">
                                                <img class="snip-thumbnail lazy-img" src="https://1.bp.blogspot.com/-QN2lgvtYZco/YN3mUSryAVI/AAAAAAAAADs/KrR-etCcvUMcPl06jopTs9pzq59IAXhMQCLcBGAsYHQ/w72-h72-p-k-no-nu/avatar.jpg">
                                            </span>
                                <div class="comment-hero">
                                    <h2 class="entry-title cmm-title">Anonymous</h2>
                                    <p class="comment-snippet">This is Third Comment Testing.</p>
                                </div>
                            </a>
                        </div>
                        <div class="cmmTool">
                            <a class="engine-link" href="#">
                                            <span class="comment-image">
                                                <img class="snip-thumbnail lazy-img" src="//blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjNhdeGnAaSnSI_ujAZ8aozZCvN7PEItpN8CpoLgRCJY_c-D6wK-Z6HsWScvs7HVK6zbfxDSh4rzM0GyVMmKnuNUReBBx6hcYq4W-k_uQANspVqU1NVwGW9c7HSYzYC8w/s113/jane-doe-img+%281%29.png=w39-h39-p-k-no-nu">
                                            </span>
                                <div class="comment-hero">
                                    <h2 class="entry-title cmm-title">Jane Doe</h2>
                                    <p class="comment-snippet">This is Second Comment Testing.</p>
                                </div>
                            </a>
                        </div>
                        <div class="cmmTool">
                            <a class="engine-link" href="#">
                                            <span class="comment-image">
                                                <img class="snip-thumbnail lazy-img" src="//blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjNhdeGnAaSnSI_ujAZ8aozZCvN7PEItpN8CpoLgRCJY_c-D6wK-Z6HsWScvs7HVK6zbfxDSh4rzM0GyVMmKnuNUReBBx6hcYq4W-k_uQANspVqU1NVwGW9c7HSYzYC8w/s113/jane-doe-img+%281%29.png=w39-h39-p-k-no-nu">
                                            </span>
                                <div class="comment-hero">
                                    <h2 class="entry-title cmm-title">Jane Doe</h2>
                                    <p class="comment-snippet">This is reply comment test.</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>


            <div class="widget Label" data-version="2" id="Label2">
                <div class="widget-title">
                    <h3 class="title">
                        {{ __('layout.sidebar_labels') }}
                    </h3>
                </div>
                <div class="widget-content cloud-label">
                    <ul>
                        @foreach(\Models\Web::allType() as $data)
                            <li>
                                <a class="label-name" href="{{url("type", ["id" => $data->id])}}">
                                    {{$data->name}}
                                    <span class="label-count">0</span>
                                </a>
                            </li>
                        @endforeach
                        @foreach(\Models\Web::allClass() as $data)
                            <li>
                                <a class="label-name" href="{{url("class", ["id" => $data->id])}}">
                                    {{$data->name}}
                                    <span class="label-count">0</span>
                                </a>
                            </li>
                        @endforeach
                        @foreach(\Models\Web::allSubject() as $data)
                            <li>
                                <a class="label-name" href="{{url("subject", ["id" => $data->id])}}">
                                    {{$data->name}}
                                    <span class="label-count">0</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>


            <div class="widget">
                <div class="widget-content">
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5588133464975020"
                            crossorigin="anonymous"></script>
                    <!-- Vuong-2 -->
                    <ins class="adsbygoogle"
                            style="display:block"
                            data-ad-client="ca-pub-5588133464975020"
                            data-ad-slot="8820179206"
                            data-ad-format="auto"
                            data-full-width-responsive="true"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>