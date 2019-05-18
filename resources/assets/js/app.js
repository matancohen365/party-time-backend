/** Auto dir */
$.fn.autodir = function() {
    return this.each(function() {
        var $this = $(this),
            text = $this.val() || $this.text(),
            dir = text.match("[\u0591-\u07FF\uFB1D-\uFDFD\uFE70-\uFEFC]") ? "rtl" : "ltr";

        $this.css("direction", dir);
    });
};

$(document).ready(function () {

    $(".single-track-title, .single-track-lyrics span").autodir();

    $("input").keyup(function () {
        $(this).autodir();

        $(".dropdown-item").autodir();
    });

    /** Search */
    var search = {

        form: $("#form-search"),

        input: $("#term"),

        btn: $(".btn-actions .btn-search"),

        btns: $(".btn-actions"),

        actions: {

            hide: function () {

                setTimeout(function () {
                    search.form.fadeOut(0);
                    search.btns.fadeIn(0);
                }, 500);

                return true;
            },

            show: function (e) {
                e.preventDefault();

                search.btns.fadeOut(0);
                search.form.fadeIn(0, function () {
                    search.input.val("").focus();
                });
            }

        },

        setup: function () {
            search.btn.click(search.actions.show);
            search.input.on("blur", search.actions.hide);
            search.form.on("submit", search.actions.hide);
        }

    };

    search.setup();


    /** TYPEAHEAD */

    $(".typeahead").typeahead({
        autoSelect: false,
        fitToElement: true,
        source: function (query, process) {
            return $.get("/typeahead/?query=" + encodeURIComponent(query), function (data) {
                return process(data);
            });
        },
        afterSelect: function () {
            search.form.submit();
        }
    });

    /** LOADING */
    var loading = {
        el: $(".loading"),
        hide: function () {
            loading.el.fadeOut(0);
        },
        show: function () {
            loading.el.fadeIn(0);
        },
        setup: function () {
            loading.hide();
            $("a").not(".skip-loading").click(loading.show);
            $("form").not(".skip-loading").submit(loading.show);
        }
    };

    loading.setup();

    /** FILE DOWNLOAD */

    $("a[href].download").click(function (e) {

        e.preventDefault();

        var xhr = new XMLHttpRequest();

        xhr.open("GET", $(this).attr("href"), true);
        xhr.responseType = "blob";

        xhr.addEventListener("load", function () {

            var file = {
                type: this.getResponseHeader("Content-Type"),
                name: decodeURIComponent(this.getResponseHeader("X-Name"))
            };

            if (this.status !== 200 || file.type.toLowerCase().indexOf("text") !== -1) {
                // Server error
                alert("Error! but .. life goes on! (server)");
            } else {
                var anchor = document.createElement("a"),
                    blob = new Blob([this.response], {type: file.type}),
                    url = window.URL.createObjectURL(blob);

                document.body.appendChild(anchor);
                anchor.style = "display: none";
                anchor.href = url;
                anchor.download = file.name;
                anchor.click();
                window.URL.revokeObjectURL(url);
            }

            loading.hide();
        });

        xhr.addEventListener("error", function () {
            // Client error
            alert("Houston, we have a problem! (browser)");
            loading.hide();
        });

        xhr.send();

    });

    /** BACK  */
    $(".back, .btn-back").click(function (e) {
        e.preventDefault();

        loading.hide();
        window.history.back();

        // if no referrer, close the window
        window.close();
    });

    $(window).bind("unload", function () {

        loading.hide();

    });

});