; (function ($) {
    $(document).ready(
        function () {
            var $form=$("#tfsb_form")
            var view_btn=document.querySelector(".view-more-btn")
            var $title=$(".title", $form)

            $title.on(
                "change", function () {
                    $form.submit()
                }
            )

            $form.on(
                "submit", function (e) {
                    e.preventDefault()

                    var searchTitle=$title.val().trim()

                    if(!searchTitle) {
                        return
                    }

                    var data={
                        title: searchTitle,
                        action: "tfsb_search_form_action",
                        _wpnonce: siteConfig.nonce,
                    }

                    $.post(
                        {
                            url: siteConfig.ajaxUrl,
                            data: data,
                            beforeSend: function () {

                                $(".load-more-posts ul").text(siteConfig.loading)
                            },
                        }
                    )
                    .done(
                        function (response) {
                            /* 
                            // Replace all occurrences of searchTitle with highlighted version
                            var pattern=new RegExp(searchTitle, "gi")
                            var highlightedHtml=response.data.html.replace( pattern, '<span class="tfsb_highligh_keyword">'+ searchTitle+ "</span>" )
                            // Replace all occurrences of "john" with "<span>john</span>"
                            highlightedHtml=highlightedHtml.replace( /john/gi, "<span>john</span>" ) 
                            */

                            $(".load-more-posts ul").html(response.data.html)
                            var btn=`<a class="view-more-btn" href="/archive-page-template/?keyword=${searchTitle}">View more</a>`

                            if(response.data.posts_found > 5 ) {
                                $(".load-more-posts ul").append(btn)
                            }

                            // change btn attr
                            // if(view_btn!==null) {

                            //     view_btn.setAttribute("data-post-title", searchTitle)
                            //     console.log(searchTitle)
                            // }

                            $(".loader").hide()
                        }
                    )
                    .fail(
                        function (e) {
                            console.log(siteConfig.error, e)

                            $(".loader").hide()
                        }
                    )
                }
            )

            // Perform ajax pagination
            $(document).on(
                "click", ".tfsb_ajax_pagination a", function (e) {
                    e.preventDefault();

                    var $ajaxurl=siteConfig.ajaxUrl
                    var $paged=$(this).text()
            
                    "&rarr;"==$paged ? ($paged=siteConfig.paged++) :($paged=$paged)
                    "&larr;"==$paged ? ($paged=siteConfig.paged--) :($paged=$paged)
                    siteConfig.paged=$paged

                    var $data={
                        paged: $(this).text(),
                        action: "archive_pagination",
                        security: siteConfig.tfsb_archive_nonce,
                    }

                    //Call the ajax for pagination
                    $.post(
                        {
                            url: $ajaxurl,
                            data: $data,
                            beforeSend: function () {

                                $(".load-more-posts ul").text(siteConfig.loading)
                            },
                        }
                    ).done(
                        function (response) {
                            response=JSON.parse(response)
                            $(".tfsb_ajax_pagination").html(response.pagination)
                            $(".load-more-posts ul").html(response.html)
                        }
                    ),
                    "json"
                }
            )
        }
    )
})(jQuery)
