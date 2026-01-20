$(function () {

    $('a[href="#toggle-search"], .navbar-bootsnipp .bootsnipp-search .input-group-btn > .btn[type="reset"]').on('click', function (event) {
        event.preventDefault();
        $('.navbar-bootsnipp .bootsnipp-search .input-group > input').val('');
        $('.navbar-bootsnipp .bootsnipp-search').toggleClass('open');
        $('a[href="#toggle-search"]').closest('li').toggleClass('active');

        if ($('.navbar-bootsnipp .bootsnipp-search').hasClass('open')) {
            /* I think .focus dosen't like css animations, set timeout to make sure input gets focus */
            setTimeout(function () {
                $('.navbar-bootsnipp .bootsnipp-search .form-control').focus();
            }, 100);
        }
    });

    $(document).on('keyup', function (event) {
        if (event.which == 27 && $('.navbar-bootsnipp .bootsnipp-search').hasClass('open')) {
            $('a[href="#toggle-search"]').trigger('click');
        }
    });

});



$(document).ready(function () {
    $('#pinBoot').pinterest_grid({
        no_columns: 4,
        padding_x: 10,
        padding_y: 10,
        margin_bottom: 50,
        single_column_breakpoint: 700
    });

    $('.slide-three-title').bind('keydown keyup', function (e) {
        $(".slide-two-preview h3").html($(this).val());
    });
    $(".slide-three-subtitle").bind('keydown keyup', function () {
        $(".slide-two-preview h5").html($(this).val());
    });
    $(".slide-three-browse").change(function (event) {

        $(".slide-3").css('background-image', 'url(' + URL.createObjectURL(event.target.files[0]) + ')');

    });

    $('.slide-two-title').bind('keydown keyup', function (e) {
        $(".slide-two-preview h3").html($(this).val());
    });
    $(".slide-two-subtitle").bind('keydown keyup', function () {
        $(".slide-two-preview h5").html($(this).val());
    });
    $(".slide-two-browse").change(function (event) {

        $(".slide-2").css('background-image', 'url(' + URL.createObjectURL(event.target.files[0]) + ')');

    });

    $(".slide-one-title").bind('keydown keyup', function () {
        $(".slide-one-preview h3").html($(this).val());
    });
    $(".slide-one-subtitle").bind('keydown keyup', function () {
        $(".slide-one-preview h5").html($(this).val());
    });
    $(".slide-one-browse").change(function (event) {

        $(".slide-1").css('background-image', 'url(' + URL.createObjectURL(event.target.files[0]) + ')');

    });


    $(".about-main-para").bind('keydown keyup', function () {
        $(".about-title").html($(this).val());
    });
    $(".about-sub-para").bind('keydown keyup', function () {
        $(".about-subtitle").html($(this).val());
    });
    $(".about-img-browse").change(function (event) {

        $(".about-img").attr("src", URL.createObjectURL(event.target.files[0]));


    });




});



(function (t, e) {
    "object" == typeof exports ? module.exports = e() : "function" == typeof define && define.amd ? define(e) : t.Spinner = e()
})(this, function () {
    "use strict";

    function t(t, e) {
        var i, n = document.createElement(t || "div");
        for (i in e) n[i] = e[i];
        return n
    }

    function e(t) {
        for (var e = 1, i = arguments.length; i > e; e++) t.appendChild(arguments[e]);
        return t
    }

    function i(t, e, i, n) {
        var o = ["opacity", e, ~~(100 * t), i, n].join("-"),
            r = .01 + 100 * (i / n),
            a = Math.max(1 - (1 - t) / e * (100 - r), t),
            s = u.substring(0, u.indexOf("Animation")).toLowerCase(),
            l = s && "-" + s + "-" || "";
        return f[o] || (c.insertRule("@" + l + "keyframes " + o + "{" + "0%{opacity:" + a + "}" + r + "%{opacity:" + t + "}" + (r + .01) + "%{opacity:1}" + (r + e) % 100 + "%{opacity:" + t + "}" + "100%{opacity:" + a + "}" + "}", c.cssRules.length), f[o] = 1), o
    }

    function n(t, e) {
        var i, n, o = t.style;
        if (void 0 !== o[e]) return e;
        for (e = e.charAt(0).toUpperCase() + e.slice(1), n = 0; d.length > n; n++)
            if (i = d[n] + e, void 0 !== o[i]) return i
    }

    function o(t, e) {
        for (var i in e) t.style[n(t, i) || i] = e[i];
        return t
    }

    function r(t) {
        for (var e = 1; arguments.length > e; e++) {
            var i = arguments[e];
            for (var n in i) void 0 === t[n] && (t[n] = i[n])
        }
        return t
    }

    function a(t) {
        for (var e = {
                x: t.offsetLeft,
                y: t.offsetTop
            }; t = t.offsetParent;) e.x += t.offsetLeft, e.y += t.offsetTop;
        return e
    }

    function s(t) {
        return this === void 0 ? new s(t) : (this.opts = r(t || {}, s.defaults, p), void 0)
    }

    function l() {
        function i(e, i) {
            return t("<" + e + ' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">', i)
        }
        c.addRule(".spin-vml", "behavior:url(#default#VML)"), s.prototype.lines = function (t, n) {
            function r() {
                return o(i("group", {
                    coordsize: u + " " + u,
                    coordorigin: -l + " " + -l
                }), {
                    width: u,
                    height: u
                })
            }

            function a(t, a, s) {
                e(f, e(o(r(), {
                    rotation: 360 / n.lines * t + "deg",
                    left: ~~a
                }), e(o(i("roundrect", {
                    arcsize: n.corners
                }), {
                    width: l,
                    height: n.width,
                    left: n.radius,
                    top: -n.width >> 1,
                    filter: s
                }), i("fill", {
                    color: n.color,
                    opacity: n.opacity
                }), i("stroke", {
                    opacity: 0
                }))))
            }
            var s, l = n.length + n.width,
                u = 2 * l,
                d = 2 * -(n.width + n.length) + "px",
                f = o(r(), {
                    position: "absolute",
                    top: d,
                    left: d
                });
            if (n.shadow)
                for (s = 1; n.lines >= s; s++) a(s, -2, "progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");
            for (s = 1; n.lines >= s; s++) a(s);
            return e(t, f)
        }, s.prototype.opacity = function (t, e, i, n) {
            var o = t.firstChild;
            n = n.shadow && n.lines || 0, o && o.childNodes.length > e + n && (o = o.childNodes[e + n], o = o && o.firstChild, o = o && o.firstChild, o && (o.opacity = i))
        }
    }
    var u, d = ["webkit", "Moz", "ms", "O"],
        f = {},
        c = function () {
            var i = t("style", {
                type: "text/css"
            });
            return e(document.getElementsByTagName("head")[0], i), i.sheet || i.styleSheet
        }(),
        p = {
            lines: 12,
            length: 7,
            width: 5,
            radius: 10,
            rotate: 0,
            corners: 1,
            color: "#000",
            direction: 1,
            speed: 1,
            trail: 100,
            opacity: .25,
            fps: 20,
            zIndex: 2e9,
            className: "spinner",
            top: "auto",
            left: "auto",
            position: "relative"
        };
    s.defaults = {}, r(s.prototype, {
        spin: function (e) {
            this.stop();
            var i, n, r = this,
                s = r.opts,
                l = r.el = o(t(0, {
                    className: s.className
                }), {
                    position: s.position,
                    width: 0,
                    zIndex: s.zIndex
                }),
                d = s.radius + s.length + s.width;
            if (e && (e.insertBefore(l, e.firstChild || null), n = a(e), i = a(l), o(l, {
                    left: ("auto" == s.left ? n.x - i.x + (e.offsetWidth >> 1) : parseInt(s.left, 10) + d) + "px",
                    top: ("auto" == s.top ? n.y - i.y + (e.offsetHeight >> 1) : parseInt(s.top, 10) + d) + "px"
                })), l.setAttribute("role", "progressbar"), r.lines(l, r.opts), !u) {
                var f, c = 0,
                    p = (s.lines - 1) * (1 - s.direction) / 2,
                    h = s.fps,
                    m = h / s.speed,
                    g = (1 - s.opacity) / (m * s.trail / 100),
                    v = m / s.lines;
                (function y() {
                    c++;
                    for (var t = 0; s.lines > t; t++) f = Math.max(1 - (c + (s.lines - t) * v) % m * g, s.opacity), r.opacity(l, t * s.direction + p, f, s);
                    r.timeout = r.el && setTimeout(y, ~~(1e3 / h))
                })()
            }
            return r
        },
        stop: function () {
            var t = this.el;
            return t && (clearTimeout(this.timeout), t.parentNode && t.parentNode.removeChild(t), this.el = void 0), this
        },
        lines: function (n, r) {
            function a(e, i) {
                return o(t(), {
                    position: "absolute",
                    width: r.length + r.width + "px",
                    height: r.width + "px",
                    background: e,
                    boxShadow: i,
                    transformOrigin: "left",
                    transform: "rotate(" + ~~(360 / r.lines * l + r.rotate) + "deg) translate(" + r.radius + "px" + ",0)",
                    borderRadius: (r.corners * r.width >> 1) + "px"
                })
            }
            for (var s, l = 0, d = (r.lines - 1) * (1 - r.direction) / 2; r.lines > l; l++) s = o(t(), {
                position: "absolute",
                top: 1 + ~(r.width / 2) + "px",
                transform: r.hwaccel ? "translate3d(0,0,0)" : "",
                opacity: r.opacity,
                animation: u && i(r.opacity, r.trail, d + l * r.direction, r.lines) + " " + 1 / r.speed + "s linear infinite"
            }), r.shadow && e(s, o(a("#000", "0 0 4px #000"), {
                top: "2px"
            })), e(n, e(s, a(r.color, "0 0 1px rgba(0,0,0,.1)")));
            return n
        },
        opacity: function (t, e, i) {
            t.childNodes.length > e && (t.childNodes[e].style.opacity = i)
        }
    });
    var h = o(t("group"), {
        behavior: "url(#default#VML)"
    });
    return !n(h, "transform") && h.adj ? l() : u = n(h, "animation"), s
});


/*!
 * Ladda 0.8.0 (2013-09-05, 18:54)
 * http://lab.hakim.se/ladda
 * MIT licensed
 *
 * Copyright (C) 2013 Hakim El Hattab, http://hakim.se
 */

(function (t, e) {
    "object" == typeof exports ? module.exports = e() : "function" == typeof define && define.amd ? define(["spin"], e) : t.Ladda = e(t.Spinner)
})(this, function (t) {
    "use strict";

    function e(t) {
        if (t === void 0) return console.warn("Ladda button target must be defined."), void 0;
        t.querySelector(".ladda-label") || (t.innerHTML = '<span class="ladda-label">' + t.innerHTML + "</span>");
        var e = i(t),
            n = document.createElement("span");
        n.className = "ladda-spinner", t.appendChild(n);
        var r, a = {
            start: function () {
                return t.setAttribute("disabled", ""), t.setAttribute("data-loading", ""), clearTimeout(r), e.spin(n), this.setProgress(0), this
            },
            startAfter: function (t) {
                return clearTimeout(r), r = setTimeout(function () {
                    a.start()
                }, t), this
            },
            stop: function () {
                return t.removeAttribute("disabled"), t.removeAttribute("data-loading"), clearTimeout(r), r = setTimeout(function () {
                    e.stop()
                }, 1e3), this
            },
            toggle: function () {
                return this.isLoading() ? this.stop() : this.start(), this
            },
            setProgress: function (e) {
                e = Math.max(Math.min(e, 1), 0);
                var n = t.querySelector(".ladda-progress");
                0 === e && n && n.parentNode ? n.parentNode.removeChild(n) : (n || (n = document.createElement("div"), n.className = "ladda-progress", t.appendChild(n)), n.style.width = (e || 0) * t.offsetWidth + "px")
            },
            enable: function () {
                return this.stop(), this
            },
            disable: function () {
                return this.stop(), t.setAttribute("disabled", ""), this
            },
            isLoading: function () {
                return t.hasAttribute("data-loading")
            }
        };
        return o.push(a), a
    }

    function n(t, n) {
        n = n || {};
        var r = [];
        "string" == typeof t ? r = a(document.querySelectorAll(t)) : "object" == typeof t && "string" == typeof t.nodeName && (r = [t]);
        for (var i = 0, o = r.length; o > i; i++)(function () {
            var t = r[i];
            if ("function" == typeof t.addEventListener) {
                var a = e(t),
                    o = -1;
                t.addEventListener("click", function () {
                    a.startAfter(1), "number" == typeof n.timeout && (clearTimeout(o), o = setTimeout(a.stop, n.timeout)), "function" == typeof n.callback && n.callback.apply(null, [a])
                }, !1)
            }
        })()
    }

    function r() {
        for (var t = 0, e = o.length; e > t; t++) o[t].stop()
    }

    function i(e) {
        var n, r = e.offsetHeight;
        r > 32 && (r *= .8), e.hasAttribute("data-spinner-size") && (r = parseInt(e.getAttribute("data-spinner-size"), 10)), e.hasAttribute("data-spinner-color") && (n = e.getAttribute("data-spinner-color"));
        var i = 12,
            a = .2 * r,
            o = .6 * a,
            s = 7 > a ? 2 : 3;
        return new t({
            color: n || "#fff",
            lines: i,
            radius: a,
            length: o,
            width: s,
            zIndex: "auto",
            top: "auto",
            left: "auto",
            className: ""
        })
    }

    function a(t) {
        for (var e = [], n = 0; t.length > n; n++) e.push(t[n]);
        return e
    }
    var o = [];
    return {
        bind: n,
        create: e,
        stopAll: r
    }
});


/*
 Ref:
 Thanks to:
 http://www.jqueryscript.net/layout/Simple-jQuery-Plugin-To-Create-Pinterest-Style-Grid-Layout-Pinterest-Grid.html
 */


/*
 Pinterest Grid Plugin
 Copyright 2014 Mediademons
 @author smm 16/04/2014
 
 usage:
 
 $(document).ready(function() {
 
 $('#blog-landing').pinterest_grid({
 no_columns: 4
 });
 
 });
 
 
 */
;
(function ($, window, document, undefined) {
    var pluginName = 'pinterest_grid',
        defaults = {
            padding_x: 10,
            padding_y: 10,
            no_columns: 3,
            margin_bottom: 50,
            single_column_breakpoint: 700
        },
        columns,
        $article,
        article_width;

    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    Plugin.prototype.init = function () {
        var self = this,
            resize_finish;

        $(window).resize(function () {
            clearTimeout(resize_finish);
            resize_finish = setTimeout(function () {
                self.make_layout_change(self);
            }, 11);
        });

        self.make_layout_change(self);

        setTimeout(function () {
            $(window).resize();
        }, 500);
    };

    Plugin.prototype.calculate = function (single_column_mode) {
        var self = this,
            tallest = 0,
            row = 0,
            $container = $(this.element),
            container_width = $container.width();
        $article = $(this.element).children();

        if (single_column_mode === true) {
            article_width = $container.width() - self.options.padding_x;
        } else {
            article_width = ($container.width() - self.options.padding_x * self.options.no_columns) / self.options.no_columns;
        }

        $article.each(function () {
            $(this).css('width', article_width);
        });

        columns = self.options.no_columns;

        $article.each(function (index) {
            var current_column,
                left_out = 0,
                top = 0,
                $this = $(this),
                prevAll = $this.prevAll(),
                tallest = 0;

            if (single_column_mode === false) {
                current_column = (index % columns);
            } else {
                current_column = 0;
            }

            for (var t = 0; t < columns; t++) {
                $this.removeClass('c' + t);
            }

            if (index % columns === 0) {
                row++;
            }

            $this.addClass('c' + current_column);
            $this.addClass('r' + row);

            prevAll.each(function (index) {
                if ($(this).hasClass('c' + current_column)) {
                    top += $(this).outerHeight() + self.options.padding_y;
                }
            });

            if (single_column_mode === true) {
                left_out = 0;
            } else {
                left_out = (index % columns) * (article_width + self.options.padding_x);
            }

            $this.css({
                'left': left_out,
                'top': top
            });
        });

        this.tallest($container);
        $(window).resize();
    };

    Plugin.prototype.tallest = function (_container) {
        var column_heights = [],
            largest = 0;

        for (var z = 0; z < columns; z++) {
            var temp_height = 0;
            _container.find('.c' + z).each(function () {
                temp_height += $(this).outerHeight();
            });
            column_heights[z] = temp_height;
        }

        largest = Math.max.apply(Math, column_heights);
        _container.css('height', largest + (this.options.padding_y + this.options.margin_bottom));
    };

    Plugin.prototype.make_layout_change = function (_self) {
        if ($(window).width() < _self.options.single_column_breakpoint) {
            _self.calculate(true);
        } else {
            _self.calculate(false);
        }
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                    new Plugin(this, options));
            }
        });
    }

})(jQuery, window, document);

//jQuery.extend(jQuery.fn.dataTableExt.oSort, {
//    "date-uk-pre": function (a) {
//        var ukDatea = a.split('/');
//        return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
//    },
//
//    "date-uk-asc": function (a, b) {
//        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
//    },
//
//    "date-uk-desc": function (a, b) {
//        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
//    }
//});