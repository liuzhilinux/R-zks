/**
 * 格式化时间。
 * @param time
 * @returns {string}
 */
const timeToLocalFormat = (time) => {
    let ts = parseInt(time);

    let v_date = new Date(ts * 1000);

    let Y = v_date.getFullYear();
    let m = v_date.getMonth() + 1;
    if (m < 10) {
        m = '0' + m;
    }
    let d = v_date.getDate();
    if (d < 10) {
        d = '0' + d;
    }
    let h = v_date.getHours();
    if (h < 10) {
        h = '0' + h;
    }
    let i = v_date.getMinutes();
    if (i < 10) {
        i = '0' + i;
    }
    let s = v_date.getSeconds();
    if (s < 10) {
        s = '0' + s;
    }

    return Y + '-' + m + '-' + d + ' ' + h + ':' + i + ':' + s;
};


/**
 * 向服务器发起请求。
 * @param fn
 * @param params
 * @returns {Promise}
 */
const httpPost = (fn, params) => new Promise((resolve, reject) => {
    let xhr = new XMLHttpRequest();
    let url = BASE_FACT + '?fn=' + fn;

    url += ((/\?/).test(url) ? "&" : "?") + (new Date()).getTime();

    // only Gecko-specific.
    // xhr.channel.loadFlags |= Components.interfaces.nsIRequest.LOAD_BYPASS_CACHE;

    xhr.timeout = 30 * 1000;

    xhr.onprogress = function (ev) {
        /*
        if (ev.lengthComputable) {
            console.log('xhr progress: ', this.responseText || this.response);
            console.log('xhr progress: ', ev.loaded / ev.total);
        }
        */
    };

    xhr.onreadystatechange = function () {
        // console.log('xhr readystatechange: ', this.readyState, this.status, xhr.statusText, this.responseText || this.response);
    };

    xhr.onload = function () {
        try {
            if (XMLHttpRequest.DONE === this.readyState) {
                if (200 === this.status) {
                    let response;

                    try {
                        response = JSON.parse(this.responseText || this.response);
                    } catch (e) {
                        // SyntaxError.
                    }

                    if (1 === parseInt(response.code)) resolve(response.data);
                    else reject(response);
                } else {
                    console.error('xhr load_err: ', this.readyState, this.status, xhr.statusText, this.responseText || this.response);
                    alert('xhr load_err: ', this.readyState, this.status, xhr.statusText, this.responseText || this.response);
                }
            }
        } catch (e) {
            console.error('xhr load_exception: ', this.readyState, this.status, xhr.statusText, this.responseText || this.response);
            alert('xhr load_exception: ', this.readyState, this.status, xhr.statusText, this.responseText || this.response);
        }
    };

    xhr.onerror = function () {
        console.error('xhr error: ', this.readyState, this.status, xhr.statusText, this.responseText || this.response);
    };

    xhr.ontimeout = function () {
        console.error('xhr timeout: ', this.readyState, this.status, xhr.statusText, this.responseText || this.response);
    };

    xhr.onabort = function () {
        console.error('xhr abort: ', this.readyState, this.status, xhr.statusText, this.responseText || this.response);
    };

    xhr.onloadend = function () {
        // console.info('xhr loadend: ', this.readyState, this.status, xhr.statusText, this.responseText || this.response);
    };

    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.send(JSON.stringify(params));
});

