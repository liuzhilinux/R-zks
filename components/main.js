/**
 * LoginBox.
 */
const LoginBox = {
    name: 'LoginBox',
    template: `
    <div id="login-box" :class="{hide: !isShow}">
        <div class="modal-box">
            <div class="modal-box-header"><p>Login</p></div>
            <div class="modal-box-content">
                <p>
                    <label for="ip_user">User:</label>
                    <input type="text" id="ip_user" name="user" v-model="loginFormData.user">
                </p>
                <p>
                    <label for="ip_pass">Pass:</label>
                    <input type="password" id="ip_pass" name="pass" v-model="loginFormData.pass"
                           @keyup.enter="handleLogin">
                </p>
            </div>
            <div class="modal-box-footer">
                <button class="modal-btn modal-btn-confirm" @click="handleLogin">Submit</button>
            </div>
        </div>
    </div>
    `,
    data: () => ({loginFormData: {user: '', pass: ''}}),
    props: {isShow: {required: true, type: Boolean}},
    methods: {
        handleLogin() {
            httpPost('login', this.loginFormData).then(data => {
                this.$emit('login-success');
            }).catch(response => {
                console.log(response);
                alert(response.msg);
            })
        }
    }
};

/**
 * Loading.
 */
const StarLoading = {
    name: 'StarLoading',
    template: `
    <div id="loading-box" v-show="isShow">
        <div class="modal-box">
            <div class="loader">
                <svg class="loader-star star1" version="1.1"
                 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                 x="0px" y="0px" width="23.172px" height="23.346px" viewBox="0 0 23.172 23.346" xml:space="preserve">
                <polygon fill="#40c4ff" points="11.586,0 8.864,8.9 0,8.9 7.193,14.447 4.471,23.346 11.586,17.84
                                                18.739,23.346 16.77,14.985 23.172,8.9 14.306,8.9"/>
            </svg>
            <svg class="loader-star star2" version="1.1"
                 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                 x="0px" y="0px" width="23.172px" height="23.346px" viewBox="0 0 23.172 23.346" xml:space="preserve">
                <polygon fill="#40c4ff" points="11.586,0 8.864,8.9 0,8.9 7.193,14.447 4.471,23.346 11.586,17.84
                                                18.739,23.346 16.77,14.985 23.172,8.9 14.306,8.9"/>
            </svg>
            <svg class="loader-star star3" version="1.1"
                 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                 x="0px" y="0px" width="23.172px" height="23.346px" viewBox="0 0 23.172 23.346" xml:space="preserve">
                <polygon fill="#40c4ff" points="11.586,0 8.864,8.9 0,8.9 7.193,14.447 4.471,23.346 11.586,17.84
                                                18.739,23.346 16.77,14.985 23.172,8.9 14.306,8.9"/>
            </svg>
            </div>
        </div>
    </div>
    `,
    props: {isShow: {type: Boolean, required: true}},
};

/**
 * 手机端头部。
 */
const MHeader = {
    name: 'MHeader',
    template: `
    <div id="cur-node-bar">
        <span id="back-btn" @click="handleOnBack">
            <svg class="back-icon" viewBox="0 0 1024 1024"
                 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <path d="M344.500344 520.998331l380.946097-402.160039c4.0216-3.92106 6.434561-9.450761
                         6.434561-15.583702 0-12.165341-9.852921-22.018262-22.018262-22.018262-6.032401 0-11.562101
                         2.5135-15.583702 6.434561L298.654099 505.414629c-4.0216 4.0216-6.434561 9.450761-6.434561
                         15.583702 0 6.032401 2.41296 11.562101 6.434561 15.583702l395.624939 417.643201c4.0216
                         4.0216 9.450761 6.434561 15.583702 6.434561 12.165341 0 22.018262-9.852921 22.018262-22.018262
                         0-6.032401-2.41296-11.562101-6.434561-15.583702L344.500344 520.998331z"></path>
            </svg>
        </span>
        <P id="cur-node-title">
            <span class="mk" v-if="isCQ && isSAQ">[选/简]</span>
            <span class="mk" v-if="isCQ && !isSAQ">[选]</span>
            <span class="mk" v-if="!isCQ && isSAQ">[简]</span>
            {{title}}
        </P>
        <span id="menu-hamburger-btn" @click="handleOnShowMenu">
            <svg class="menu-hamburger-icon" viewBox="0 0 1024 1024"
                 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <path d="M128 213.333333h768a42.666667 42.666667 0 0 1 0 85.333334H128a42.666667 42.666667 0 1 1
                         0-85.333334z m0 256h597.333333a42.666667 42.666667 0 0 1 0 85.333334H128a42.666667 42.666667
                         0 0 1 0-85.333334z m0 256h341.333333a42.666667 42.666667 0 0 1 0 85.333334H128a42.666667
                         42.666667 0 0 1 0-85.333334z"></path>
            </svg>
        </span>
    </div>
    `,
    props: {
        title: {required: true, type: String},
        isCQ: {required: true, type: Boolean},
        isSAQ: {required: true, type: Boolean}
    },
    methods: {
        handleOnBack() {
            this.$emit('on-back');
        },
        handleOnShowMenu() {
            this.$emit('on-show-menu');
        }
    }
};

/**
 * 菜单。
 */
const HBMenu = {
    name: 'HBMenu',
    template: `
    <!-- a id="mHamburger" href="javascript: void(0);">Op</a -->
    <div id="HBMenu">
        <!-- 完全展开 flyShow fade-in -->
        <!-- 收回过程 flyShow fade-out -->
        <!-- 完全收回 flyHide fade-out -->
        <div id="HBFlyoutBehind"
             :class="{
                          flyShow, flyHide,
                          'fade-out': fade_out, 'fade-in': fade_in 
                      }"></div>
        <!-- 完全展开 flyouttop_ltr flyShow slidein_ltr -->
        <!-- 收回过程 flyouttop_ltr flyShow slideout_ltr -->
        <!-- 完全收回 flyouttop_ltr flyShow -->
        <div id="HBFlyoutTop"
             :class="['flyouttop_ltr', 'flyShow', {slidein_ltr, slideout_ltr}]">
            <div id="HBleft"></div>
            <div id="HBright">
                <div id="HBScroller">
                    <div id="HBContent">
                        <p id="HBTopBar">
                            <a id="HBCloseBtn" @click="handleOnClose" href="javascript: void(0);">
                                <svg class="HBCloseIcon" viewBox="0 0 1024 1024" version="1.1"
                                     xmlns="http://www.w3.org/2000/svg"  xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <path d="M176.662 817.173c-8.19 8.471-7.96 21.977 0.51 30.165 8.472 8.19 21.978
                                             7.96 30.166-0.51l618.667-640c8.189-8.472
                                             7.96-21.978-0.511-30.166-8.471-8.19-21.977-7.96-30.166
                                             0.51l-618.666 640z"></path>
                                    <path d="M795.328 846.827c8.19 8.471 21.695 8.7 30.166 0.511 8.471-8.188 8.7-21.694
                                             0.511-30.165l-618.667-640c-8.188-8.471-21.694-8.7-30.165-0.511-8.471
                                             8.188-8.7 21.694-0.511 30.165l618.666 640z"></path>
                                </svg>
                            </a>
                        </p>
                        <p id="HBCurTitle" @click="handleOnLoad(curNode.parent_node_id)">
                            <span class="mk" v-if="curNode.is_cq && curNode.is_saq">[选/简]</span>
                            <span class="mk" v-if="curNode.is_cq && !curNode.is_saq">[选]</span>
                            <span class="mk" v-if="!curNode.is_cq && curNode.is_saq">[简]</span>
                            {{curNode.node_title}}
                        </p>
                        <ul id="HBNodeList">
                            <li v-for="item, idx in list" :key="idx" 
                                :class="{cur: item.id === curId}" @click="handleOnLoad(item.id)">
                                <span class="mk" v-if="item.is_cq && item.is_saq">[选/简]</span>
                                <span class="mk" v-if="item.is_cq && !item.is_saq">[选]</span>
                                <span class="mk" v-if="!item.is_cq && item.is_saq">[简]</span>
                                {{item.node_title}}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `,
    data: () => ({
        flyShow: false,
        flyHide: true,

        fade_in: false,
        fade_out: true,

        slidein_ltr: false,
        slideout_ltr: false
    }),
    props: {
        isOpen: {required: true, type: Boolean},
        curId: {required: true, type: Number},
        curNode: {required: true, type: Object},
        list: {required: true, type: Array}
    },
    watch: {
        isOpen(val, oldVal) {
            if (val) this.open();
            else this.close();
        }
    },
    methods: {
        open() {
            this.flyShow = true;
            this.flyHide = false;
            this.fade_in = true;
            this.fade_out = false;
            this.slidein_ltr = true;
            this.slideout_ltr = false;

            this.$emit('fade-in');
        },
        close() {
            this.flyShow = true;
            this.flyHide = false;
            this.fade_in = false;
            this.fade_out = true;
            this.slidein_ltr = false;
            this.slideout_ltr = true;

            let that = this;

            setTimeout(() => {
                that.flyShow = false;
                that.flyHide = true;
                that.fade_in = false;
                that.fade_out = true;
                that.slidein_ltr = false;
                that.slideout_ltr = false;
            }, 1000);

            this.$emit('fade-out');
        },
        handleOnClose() {
            this.$emit('on-close');
        },
        handleOnLoad(id) {
            this.$emit('on-load', {id});
        }
    }
};

/**
 * 子菜单。
 */
const SubNodesList = {
    name: 'SubNodesList',
    template: `
    <div id="sub-nodes-list-box">
        <ul id="sub-nodes-list">
            <li v-for="item, idx in list" :key="idx">
                <a href="javascript: void(0);" @click="handleOnLoad(item)">
                    <span class="mk" v-if="item.is_cq && item.is_saq">[选/简]</span>
                    <span class="mk" v-if="item.is_cq && !item.is_saq">[选]</span>
                    <span class="mk" v-if="!item.is_cq && item.is_saq">[简]</span>
                    {{item.node_title}}
                </a>
            </li>
        </ul>
    </div>    
    `,
    props: {list: {required: true, type: Array}},
    methods: {
        handleOnLoad(item) {
            this.$emit('on-load', item);
        }
    }
};

