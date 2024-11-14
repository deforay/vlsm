var $jscomp = $jscomp || {}; $jscomp.scope = {}; $jscomp.checkStringArgs = function (e, g, k) { if (null == e) throw new TypeError("The 'this' value for String.prototype." + k + " must not be null or undefined"); if (g instanceof RegExp) throw new TypeError("First argument to String.prototype." + k + " must not be a regular expression"); return e + "" }; $jscomp.ASSUME_ES5 = !1; $jscomp.ASSUME_NO_NATIVE_MAP = !1; $jscomp.ASSUME_NO_NATIVE_SET = !1;
$jscomp.defineProperty = $jscomp.ASSUME_ES5 || "function" == typeof Object.defineProperties ? Object.defineProperty : function (e, g, k) { e != Array.prototype && e != Object.prototype && (e[g] = k.value) }; $jscomp.getGlobal = function (e) { return "undefined" != typeof window && window === e ? e : "undefined" != typeof global && null != global ? global : e }; $jscomp.global = $jscomp.getGlobal(this);
$jscomp.polyfill = function (e, g, k, h) { if (g) { k = $jscomp.global; e = e.split("."); for (h = 0; h < e.length - 1; h++) { var f = e[h]; f in k || (k[f] = {}); k = k[f] } e = e[e.length - 1]; h = k[e]; g = g(h); g != h && null != g && $jscomp.defineProperty(k, e, { configurable: !0, writable: !0, value: g }) } };
$jscomp.polyfill("String.prototype.startsWith", function (e) { return e ? e : function (e, k) { var h = $jscomp.checkStringArgs(this, e, "startsWith"); e += ""; var f = h.length, g = e.length; k = Math.max(0, Math.min(k | 0, h.length)); for (var q = 0; q < g && k < f;)if (h[k++] != e[q++]) return !1; return q >= g } }, "es6", "es3");
var BrowserPrint = function () {
    function e(a, c) { var b = new XMLHttpRequest; "withCredentials" in b ? b.open(a, c, !0) : "undefined" != typeof XDomainRequest ? (b = new XDomainRequest, b.open(a, c)) : b = null; return b } function g(a, c, b, d) { void 0 !== a && (void 0 === b && (b = a.sendFinishedCallback), void 0 === d && (d = a.sendErrorCallback)); return h(c, b, d) } function k(a, c, b) { void 0 === c && (c = f.defaultSuccessCallback); void 0 === b && (b = f.defaultErrorCallback); return h(a, c, b) } function h(a, c, b) {
        a.onreadystatechange = function () {
            a.readyState === XMLHttpRequest.DONE &&
                200 === a.status ? "" === a.responseType ? c(a.responseText) : c(a.response) : a.readyState === XMLHttpRequest.DONE && (b ? b(a.response) : console.log("error occurred with no errorCallback set."))
        }; return a
    } var f = {}, p = {}, q = /^((?!chrome|android).)*safari/i.test(navigator.userAgent); navigator.userAgent.indexOf("Trident/7.0"); var m = "http://127.0.0.1:9100/"; q && "https:" === location.protocol && (m = "https://127.0.0.1:9101/"); f.Device = function (a) {
        var c = this; this.name = a.name; this.deviceType = a.deviceType; this.connection = a.connection;
        this.uid = a.uid; this.version = 2; this.provider = a.provider; this.manufacturer = a.manufacturer; this.readRetries = "bluetooth" === this.connection ? 1 : 0; this.sendErrorCallback = function (b) { }; this.sendFinishedCallback = function (b) { }; this.readErrorCallback = function (b) { }; this.readFinishedCallback = function (b) { }; this.send = function (b, a, l) {
            var d = e("POST", m + "write"); d && (g(c, d, a, l), d.send(JSON.stringify({
                device: {
                    name: this.name, uid: this.uid, connection: this.connection, deviceType: this.deviceType, version: this.version, provider: this.provider,
                    manufacturer: this.manufacturer
                }, data: b
            })))
        }; this.sendUrl = function (b, a, l, r) { var d = e("POST", m + "write"); d && (g(c, d, a, l), b = { device: { name: this.name, uid: this.uid, connection: this.connection, deviceType: this.deviceType, version: this.version, provider: this.provider, manufacturer: this.manufacturer }, url: b }, null != r && void 0 != r && (b.options = r), d.send(JSON.stringify(b))) }; this.sendFile = function (b, a, l) {
            if ("string" === typeof b) f.loadFileFromUrl(b, function (b) { c.sendFile(b, a, l) }, l); else {
                var d = e("POST", m + "write"); if (d) {
                    d.responseType =
                        "text"; k(d, a, l); var n = new FormData, g = {}; g.device = c; n.append("json", JSON.stringify(g)); n.append("blob", b); d.send(n)
                }
            }
        }; this.convertAndSendFile = function (b, a, l, c) { c || (c = {}); c.action || (c.action = "print"); f.convert(b, this, c, a, l) }; this.read = function (b, a) {
            var d = e("POST", m + "read"); d && (void 0 !== c && (void 0 === b && (b = c.readFinishedCallback), void 0 === a && (a = c.readErrorCallback)), h(d, b, a), d.send(JSON.stringify({
                device: {
                    name: this.name, uid: this.uid, connection: this.connection, deviceType: this.deviceType, version: this.version,
                    provider: this.provider, manufacturer: this.manufacturer
                }
            })))
        }; this.readUntilStringReceived = function (a, d, c, e, f) { f || (f = ""); void 0 === e && (e = this.readRetries); d = function (b, d, c, l, e) { return function (f) { if (f && 0 !== f.length) l = 0; else if (0 >= l) { d(e); return } f = e + f; "" !== a && -1 < f.indexOf(a) ? d(f) : b.readUntilStringReceived(a, d, c, l - 1, f) } }(this, d, c, e, f); this.read(d, c) }; this.readAllAvailable = function (a, d, c) { this.readUntilStringReceived("", a, d, c) }; this.sendThenRead = function (a, d, c) {
            this.send(a, function (a) {
                return function () {
                    a.read(d,
                        c)
                }
            }(this), c)
        }; this.sendThenReadUntilStringReceived = function (a, d, c, e, f) { this.send(a, function (a) { return function () { a.readUntilStringReceived(d, c, e, f) } }(this), e) }; this.sendThenReadAllAvailable = function (a, d, c, e) { this.send(a, function (a) { return function () { a.readUntilStringReceived("", d, c, e) } }(this), c) }
    }; f.defaultSuccessCallback = function () { }; f.defaultErrorCallback = function () { }; f.ApplicationConfiguration = function () { this.application = { version: "1.2.0.3", build_number: 3, api_level: 2, platform: "", supportedConversions: {} } };
    f.getLocalDevices = function (a, c, b) { var d = e("GET", m + "available"); d && (finishedFunction = function (c) { response = c; response = JSON.parse(response); for (var d in response) if (response.hasOwnProperty(d) && response[d].constructor === Array) for (arr = response[d], c = 0; c < arr.length; ++c)arr[c] = new f.Device(arr[c]); void 0 === b ? a(response) : (response.hasOwnProperty(b) || (response[b] = []), a(response[b])) }, k(d, finishedFunction, c), d.send()) }; f.getDefaultDevice = function (a, c, b) {
        var d = "default"; void 0 !== a && null != a && (d = d + "?type=" + a);
        if (a = e("GET", m + d)) finishedFunction = function (a) { response = a; "" === response ? c(null) : (response = JSON.parse(response), a = new f.Device(response), c(a)) }, a = k(a, finishedFunction, b), a.send()
    }; f.getApplicationConfiguration = function (a, c) { var b = e("GET", m + "config"); b && (finishedFunction = function (b) { response = b; "" === response ? a(null) : (response = JSON.parse(response), a(response)) }, k(b, finishedFunction, c), b.send()) }; f.readOnInterval = function (a, c, b) {
        if (void 0 === b || 0 === b) b = 1; readFunc = function () {
            a.read(function (d) {
                c(d); p[a] =
                    setTimeout(readFunc, b)
            }, function (c) { p[a] = setTimeout(readFunc, b) })
        }; p[a] = setTimeout(readFunc, b)
    }; f.stopReadOnInterval = function (a) { p[a] && clearTimeout(p[a]) }; f.bindFieldToReadData = function (a, c, b, d) { f.readOnInterval(a, function (a) { "" !== a && (c.value = a, void 0 !== d && null != d && d()) }, b) }; f.loadFileFromUrl = function (a, c, b) { request = e("get", a); console.log("ResponseType: " + request.responseType); request.responseType = "blob"; k(request, c, b); request.send() }; f.convert = function (a, c, b, d, l) {
        if (a) if ("string" === typeof a) f.loadFileFromUrl(a,
            function (e) { b.fromFormat || (b.fromFormat = a.substring(a.length - 3)); f.convert(e, c, b, d, l) }, l); else {
            var g = e("POST", m + "convert"); a.type && (a.type.startsWith("image/") || a.type.startsWith("application/")) && (b.fromFormat = a.type.toLowerCase().replace("image/", "").replace("application/", "").replace("x-ms-", "")); if (g) {
                g.responseType = "text"; var h = void 0; void 0 != d && (h = function (a) { d(JSON.parse(a)) }); k(g, h, l); h = new FormData; var t = {}; null != b && void 0 !== b && (t.options = b); c && (t.device = c); h.append("json", JSON.stringify(t));
                h.append("blob", a); g.send(h)
            }
        } else l ? l("Resource not specified") : f.defaultErrorCallback("Resource not specified")
    }; f.scanImage = function (a, c, b, d) {
        if (a) if ("string" === typeof a) f.loadFileFromUrl(a, function (e) { c.format || (c.format = a.substring(a.length - 3)); f.scanImage(e, c, b, d) }, d); else {
            var g = e("POST", m + "convert/scan"); a.type && (a.type.startsWith("image/") || a.type.startsWith("application/")) && (c.format = a.type.toLowerCase().replace("image/", "").replace("application/", "").replace("x-ms-", "")); if (g) {
                g.responseType =
                    "text"; var h = void 0; void 0 != b && (h = function (a) { b(JSON.parse(a)) }); k(g, h, d); h = new FormData; var n = {}; n.options = c; h.append("json", JSON.stringify(n)); h.append("blob", a); g.send(h)
            }
        } else d ? d("Resource not specified") : f.defaultErrorCallback("Resource not specified")
    }; return f
}();


var $jscomp = $jscomp || {}; $jscomp.scope = {}; $jscomp.ASSUME_ES5 = !1; $jscomp.ASSUME_NO_NATIVE_MAP = !1; $jscomp.ASSUME_NO_NATIVE_SET = !1; $jscomp.defineProperty = $jscomp.ASSUME_ES5 || "function" == typeof Object.defineProperties ? Object.defineProperty : function (e, h, g) { e != Array.prototype && e != Object.prototype && (e[h] = g.value) }; $jscomp.getGlobal = function (e) { return "undefined" != typeof window && window === e ? e : "undefined" != typeof global && null != global ? global : e }; $jscomp.global = $jscomp.getGlobal(this); $jscomp.SYMBOL_PREFIX = "jscomp_symbol_";
$jscomp.initSymbol = function () { $jscomp.initSymbol = function () { }; $jscomp.global.Symbol || ($jscomp.global.Symbol = $jscomp.Symbol) }; $jscomp.Symbol = function () { var e = 0; return function (h) { return $jscomp.SYMBOL_PREFIX + (h || "") + e++ } }();
$jscomp.initSymbolIterator = function () { $jscomp.initSymbol(); var e = $jscomp.global.Symbol.iterator; e || (e = $jscomp.global.Symbol.iterator = $jscomp.global.Symbol("iterator")); "function" != typeof Array.prototype[e] && $jscomp.defineProperty(Array.prototype, e, { configurable: !0, writable: !0, value: function () { return $jscomp.arrayIterator(this) } }); $jscomp.initSymbolIterator = function () { } }; $jscomp.arrayIterator = function (e) { var h = 0; return $jscomp.iteratorPrototype(function () { return h < e.length ? { done: !1, value: e[h++] } : { done: !0 } }) };
$jscomp.iteratorPrototype = function (e) { $jscomp.initSymbolIterator(); e = { next: e }; e[$jscomp.global.Symbol.iterator] = function () { return this }; return e }; $jscomp.makeIterator = function (e) { $jscomp.initSymbolIterator(); var h = e[Symbol.iterator]; return h ? h.call(e) : $jscomp.arrayIterator(e) };
$jscomp.polyfill = function (e, h, g, l) { if (h) { g = $jscomp.global; e = e.split("."); for (l = 0; l < e.length - 1; l++) { var c = e[l]; c in g || (g[c] = {}); g = g[c] } e = e[e.length - 1]; l = g[e]; h = h(l); h != l && null != h && $jscomp.defineProperty(g, e, { configurable: !0, writable: !0, value: h }) } }; $jscomp.FORCE_POLYFILL_PROMISE = !1;
$jscomp.polyfill("Promise", function (e) {
    function h() { this.batch_ = null } function g(b) { return b instanceof c ? b : new c(function (a, f) { a(b) }) } if (e && !$jscomp.FORCE_POLYFILL_PROMISE) return e; h.prototype.asyncExecute = function (b) { null == this.batch_ && (this.batch_ = [], this.asyncExecuteBatch_()); this.batch_.push(b); return this }; h.prototype.asyncExecuteBatch_ = function () { var b = this; this.asyncExecuteFunction(function () { b.executeBatch_() }) }; var l = $jscomp.global.setTimeout; h.prototype.asyncExecuteFunction = function (b) {
        l(b,
            0)
    }; h.prototype.executeBatch_ = function () { for (; this.batch_ && this.batch_.length;) { var b = this.batch_; this.batch_ = []; for (var a = 0; a < b.length; ++a) { var f = b[a]; delete b[a]; try { f() } catch (k) { this.asyncThrow_(k) } } } this.batch_ = null }; h.prototype.asyncThrow_ = function (b) { this.asyncExecuteFunction(function () { throw b; }) }; var c = function (b) { this.state_ = 0; this.result_ = void 0; this.onSettledCallbacks_ = []; var a = this.createResolveAndReject_(); try { b(a.resolve, a.reject) } catch (f) { a.reject(f) } }; c.prototype.createResolveAndReject_ =
        function () { function b(b) { return function (k) { f || (f = !0, b.call(a, k)) } } var a = this, f = !1; return { resolve: b(this.resolveTo_), reject: b(this.reject_) } }; c.prototype.resolveTo_ = function (b) { if (b === this) this.reject_(new TypeError("A Promise cannot resolve to itself")); else if (b instanceof c) this.settleSameAsPromise_(b); else { a: switch (typeof b) { case "object": var a = null != b; break a; case "function": a = !0; break a; default: a = !1 }a ? this.resolveToNonPromiseObj_(b) : this.fulfill_(b) } }; c.prototype.resolveToNonPromiseObj_ = function (b) {
            var a =
                void 0; try { a = b.then } catch (f) { this.reject_(f); return } "function" == typeof a ? this.settleSameAsThenable_(a, b) : this.fulfill_(b)
        }; c.prototype.reject_ = function (b) { this.settle_(2, b) }; c.prototype.fulfill_ = function (b) { this.settle_(1, b) }; c.prototype.settle_ = function (b, a) { if (0 != this.state_) throw Error("Cannot settle(" + b + ", " + a | "): Promise already settled in state" + this.state_); this.state_ = b; this.result_ = a; this.executeOnSettledCallbacks_() }; c.prototype.executeOnSettledCallbacks_ = function () {
            if (null != this.onSettledCallbacks_) {
                for (var b =
                    this.onSettledCallbacks_, a = 0; a < b.length; ++a)b[a].call(), b[a] = null; this.onSettledCallbacks_ = null
            }
        }; var d = new h; c.prototype.settleSameAsPromise_ = function (b) { var a = this.createResolveAndReject_(); b.callWhenSettled_(a.resolve, a.reject) }; c.prototype.settleSameAsThenable_ = function (b, a) { var f = this.createResolveAndReject_(); try { b.call(a, f.resolve, f.reject) } catch (k) { f.reject(k) } }; c.prototype.then = function (b, a) {
            function f(a, f) { return "function" == typeof a ? function (f) { try { k(a(f)) } catch (n) { d(n) } } : f } var k, d, e = new c(function (a,
                f) { k = a; d = f }); this.callWhenSettled_(f(b, k), f(a, d)); return e
        }; c.prototype.catch = function (b) { return this.then(void 0, b) }; c.prototype.callWhenSettled_ = function (b, a) { function f() { switch (k.state_) { case 1: b(k.result_); break; case 2: a(k.result_); break; default: throw Error("Unexpected state: " + k.state_); } } var k = this; null == this.onSettledCallbacks_ ? d.asyncExecute(f) : this.onSettledCallbacks_.push(function () { d.asyncExecute(f) }) }; c.resolve = g; c.reject = function (b) { return new c(function (a, f) { f(b) }) }; c.race = function (b) {
            return new c(function (a,
                f) { for (var k = $jscomp.makeIterator(b), d = k.next(); !d.done; d = k.next())g(d.value).callWhenSettled_(a, f) })
        }; c.all = function (b) { var a = $jscomp.makeIterator(b), f = a.next(); return f.done ? g([]) : new c(function (b, d) { function k(a) { return function (f) { c[a] = f; e--; 0 == e && b(c) } } var c = [], e = 0; do c.push(void 0), e++, g(f.value).callWhenSettled_(k(c.length - 1), d), f = a.next(); while (!f.done) }) }; return c
}, "es6", "es3"); function dbg(e) { }
var Zebra = function () {
    function e(c, d) { if (d instanceof g.Printer.Status && l[c.device.uid]) { if (d.offline) { if (c.errors++, c.errors < c.errorsForOffline) return } else c.errors = 0; var b = l[c.device.uid].status, a = JSON.stringify(b); c.status = d; statusStr = JSON.stringify(d); if (statusStr !== a) c.onchange(b, d) } } function h(c) { return 2 !== c.charCodeAt(0) || 3 !== c.charCodeAt(c.length - 1) ? (dbg("Response did not contain proper control characters"), !1) : !0 } var g = {}, l = {}; setInterval(function () {
        for (var c in l) l.hasOwnProperty(c) && (c =
            l[c], function (d) { d.device.getStatus(function (b) { e(d, b) }, function (b) { e(d, new g.Printer.Status("")) }) }(c))
    }, 2E3); g.Printer = function (c) {
        BrowserPrint.Device.call(this, c); g.Printer.Status = function (a) {
            this.raw = a; this.isFlagSet = function (a) { return "1" === this.raw.charAt(a) }; this.offline = !1; a || (a = ""); a = a.trim(); h(a) ? (this.offline = !1, this.paperOut = this.isFlagSet(5), this.paused = this.isFlagSet(7), this.headOpen = this.isFlagSet(43), this.ribbonOut = this.isFlagSet(45)) : (this.offline = !0, this.ribbonOut = this.headOpen = this.paused =
                this.paperOut = !1); this.isPrinterReady = function () { return !(this.paperOut || this.paused || this.headOpen || this.ribbonOut || this.offline) }; this.getMessage = function () { return this.isPrinterReady() ? "Ready" : this.offline ? "Offline" : this.paperOut ? "Paper Out" : this.headOpen ? "Head Open" : this.ribbonOut ? "Ribbon Out" : this.paused ? "Paused" : "Ready" }
        }; g.Printer.Info = function (a) { if (!a) throw "Invalid Response"; this.raw = a; a = a.trim(); if (!h(a)) throw "Invalid Response"; a = a.split(","); this.model = a[0].substring(1); this.firmware = a[1] };
        g.Printer.Configuration = function (a) {
            if (!a) throw "Invalid Response"; this.raw = a = a.trim(); this.settings = {}; if (!h(a)) throw "Invalid Response"; a = a.replace(String.fromCharCode(2), ""); a = a.replace(String.fromCharCode(3), ""); a = a.split("\n"); for (var f = 0; f < a.length; ++f) { var b = a[f].trim(), d = b.substring(20); b = b.substring(0, 20).trim(); this.settings[d] = b } this.darkness = parseFloat(this.settings.DARKNESS); this.printSpeed = parseInt(this.settings["PRINT SPEED"].replace("IPS", "").trim()); this.printWidth = parseInt(this.settings["PRINT WIDTH"]);
            this.labelLength = parseInt(this.settings["LABEL LENGTH"]); this.firmwareVersion = this.settings.FIRMWARE.replace("<-", "").trim(); this.linkOSVersion = this.settings.hasOwnProperty("LINK-OS VERSION") ? this.settings["LINK-OS VERSION"] : "0"
        }; var d = this; this.configuration = void 0; this.device_request_queue = []; this.clearRequestQueue = function () { var a = this.device_request_queue[0]; this.device_request_queue = []; a && a.started && (this.device_request_queue[0] = a) }; this.Request = function (a, f, b, c, e) {
            this.type = a; this.command = f;
            this.received = b; this.success = c; this.error = function (a) { return function (f) { a(f); d.executeNextRequest() } }(e); this.execute = function () { this.started = !0; "info" == this.type || "config" == this.type || "status" == this.type ? d.sendThenReadUntilStringReceived(this.command, String.fromCharCode(3), this.received, this.error) : d.sendThenReadAllAvailable(this.command, this.received, this.error) }
        }; this.executeNextRequest = function () { d.device_request_queue.shift(); d.executeRequest() }; this.executeRequest = function () {
            dbg("Requests in queue: " +
                d.device_request_queue.length); 0 < d.device_request_queue.length && (dbg("Executing next request..."), d.device_request_queue[0].execute())
        }; this.queueRequest = function (a) { dbg("Queueing request " + a.type + ": " + d.device_request_queue.length); d.device_request_queue.push(a); 1 === d.device_request_queue.length && a.execute() }; this.onStatusResponse = function (a) {
            dbg("received status response"); var f = void 0; try { f = new g.Printer.Status(a) } catch (k) { a = d.device_request_queue[0], a.error(k), d.executeNextRequest() } for (; 0 < d.device_request_queue.length;)if (a =
                d.device_request_queue[0], "status" === a.type) dbg("delivering status..."), a.success(f), d.device_request_queue.shift(); else { dbg("delivered to all status requests."); break } d.executeRequest()
        }; this.onResponse = function (a, f) { dbg("received info response"); var b = d.device_request_queue[0]; if (void 0 != f) try { a = new f(a) } catch (m) { b.error && b.error(m); d.executeNextRequest(); return } b.success && b.success(a); d.executeNextRequest() }; this.onSGDResponse = function (a) { dbg("received sgd response"); d.onResponse(a) }; this.onInfoResponse =
            function (a) { dbg("received info response"); d.onResponse(a, g.Printer.Info) }; this.onConfigurationResponse = function (a) { dbg("received config response"); try { d.configuration = new g.Printer.Configuration(a) } catch (f) { } d.onResponse(a, g.Printer.Configuration) }; this.setSGD = function (a, f, b, c) { if (!b && !c) return new Promise(function (b, c) { d.setSGD(a, f, b, c) }); d.send('! U1 setvar "' + a + '" "' + f + '"\r\n', b, c) }; this.getSGD = function (a, b, c) {
                if (!b && !c) return new Promise(function (b, f) { d.getSGD(a, b, f) }); b = new this.Request("sgd",
                    '! U1 getvar "' + a + '"\r\n', this.onSGDResponse, b, c); this.queueRequest(b)
            }; this.setThenGetSGD = function (a, b, c, e) { if (!c && !e) return new Promise(function (f, c) { d.setThenGetSGD(a, b, f, c) }); this.setSGD(a, b, function () { d.getSGD(a, c, e) }, e) }; this.getInfo = function (a, b) { if (!a && !b) return new Promise(function (a, b) { d.getInfo(a, b) }); a = new this.Request("info", "~hi\r\n", this.onInfoResponse, a, b); this.queueRequest(a) }; this.getConfiguration = function (a, b) {
                if (!a && !b) return new Promise(function (a, b) { d.getConfiguration(a, b) });
                a = new this.Request("config", "^XA^HH^XZ", this.onConfigurationResponse, a, b); this.queueRequest(a)
            }; this.getStatus = function (a, b) { if (!a && !b) return new Promise(function (a, b) { d.getStatus(a, b) }); a = new this.Request("status", "~hs\r\n", this.onStatusResponse, a, b); d.queueRequest(a) }; this.query = function (a, b, c) { if (!b && !c) return new Promise(function (b, c) { d.query(a, b, c) }); b = new this.Request("", a, this.onResponse, b, c); this.queueRequest(b) }; this.isPrinterReady = function (a, b) {
                if (!a && !b) return new Promise(function (a, b) {
                    d.isPrinterReady(a,
                        b)
                }); this.getStatus().then(function (c) { c.isPrinterReady() ? a(c.getMessage()) : b(c.getMessage()) })
            }; this.printImageAsLabel = function (a, c, e, g) { if (!e && !g) return new Promise(function (b, f) { d.printImageAsLabel(a, c, b, f) }); b().then(function (b) { c.fitTo = { width: b.printWidth, height: b.labelLength }; c.action = "print"; BrowserPrint.convert(a, d, c, e, g) }).catch(g) }; this.getConvertedResource = function (a, c, e, g) {
                if (!e && !g) return new Promise(function (b, f) { d.getConvertedResource(a, c, b, f) }); b().then(function (b) {
                    c.action = "return";
                    BrowserPrint.convert(a, d, c, e, g)
                }).catch(g)
            }; this.storeConvertedResource = function (a, c, e, g) { if (!e && !g) return new Promise(function (b, e) { d.storeConvertedResource(a, c, b, e) }); b().then(function (b) { c.action = "store"; BrowserPrint.convert(a, d, c, e, g) }).catch(g) }; var b = function () { return new Promise(function (a, b) { if (d.configuration) a(d.configuration); else return d.getConfiguration().then(function (a) { d.configuration = a; return d.configuration }).catch(function (a) { b(a) }) }) }; this.configTimeout = function () {
                d.configuration ||
                d.getConfiguration().then(function (a) { return d.configuration = a }).catch(function () { setTimeout(d.configTimeout, 1E3) })
            }; this.configTimeout()
    }; g.watch = function (c, d, b) { b || (b = 2); l[c.uid] = { device: c, status: "", onchange: d, errors: 0, errorsForOffline: b } }; g.stopWatching = function (c) { delete l[c.uid] }; return g
}();
