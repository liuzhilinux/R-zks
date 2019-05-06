iview.lang('en-US');

const PbxMain = {
    template: '#pbx-main',
    data: () => ({
        columns: [
            // {title: '#', slot: 'num', width: 54, align: 'center'},
            {type: 'index', width: 54, align: 'center'},
            {title: 'Node Title', slot: 'node_title', minWidth: 240},
            {title: 'CQ', slot: 'is_cq', width: 60},
            {title: 'SAQ', slot: 'is_saq', width: 68},
            {title: 'Updated Time', slot: 'update_time', width: 150},
            {title: 'Created Time', slot: 'create_time', width: 150},
            {title: 'Action', slot: 'action', width: 240},
        ],

        title: 'Main',
        list: [],

        isLoading: false,
        isMovingLoading: false,
    }),
    props: {
        id: {type: Number, required: true},
        parentNodeId: {type: Number, required: true}
    },
    methods: {
        /**
         * 点击添加新项目按钮。
         */
        addNew() {
            this.$emit('add-new');
        },

        /**
         * 根据父节点 ID 获取父节点标题和子节点列表。
         * @param parentId 父节点 ID 。
         */
        loadNodesList(parentId) {
            this.isLoading = true;
            httpPost('get_nodes', {id: parentId}).then(data => {
                let curNode = data.current_node;
                this.list = false === data.sub_nodes ? [] : data.sub_nodes;
                this.title = curNode.node_title;

                curNode.cur_sequence = false === data.sub_nodes ? 0 : data.sub_nodes.length;
                this.$emit('on-refresh-list', curNode);

                this.isLoading = false;
            }).catch(response => {
                console.log(response);
                iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                if (-57 === response.code) {
                    this.$emit('show-login-box');
                }

                this.isLoading = false;
            });
        },

        /**
         * 当双击表格项的时候，刷新列表获取当前所双击的表格项。
         * @param row
         * @param idx
         */
        onTurnToNode(row, idx) {
            this.loadNodesList(row.id);
        },

        /**
         * 点击返回上一级按钮，获取上一级节点数据。
         */
        onReturnBack() {
            this.loadNodesList(this.parentNodeId);
        },

        /**
         * 刷新列表。
         */
        refreshList(parentId = null) {
            this.loadNodesList(parentId || this.id);
        },

        /**
         * 切换选择题标签。
         * @param row
         */
        setCQ(row) {
            let isCQ = row.is_cq = row.is_cq === 0 ? 1 : 0;

            httpPost('update_node_qtype', {id: row.id, is_cq: isCQ}).then(data => {
                if (data) iview.Message.success('Success!');
            }).catch(response => {
                console.log(response);
                iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                if (-57 === response.code) {
                    this.$emit('show-login-box');
                }
            });
        },

        /**
         * 切换简答题标签。
         * @param row
         */
        setSAQ(row) {
            let isSAQ = row.is_saq = row.is_saq === 0 ? 1 : 0;

            httpPost('update_node_qtype', {id: row.id, is_saq: isSAQ}).then(data => {
                if (data) iview.Message.success('Success!');
            }).catch(response => {
                console.log(response);
                iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                if (-57 === response.code) {
                    this.$emit('show-login-box');
                }
            });
        },

        /**
         * 点击编辑按钮打开对应节点，获取节点内容并进行编辑。
         * @param row 当前行数据
         */
        openEditor(row) {
            this.$emit('open-editor', {id: row.id});
        },

        /**
         * 向上移动。
         * @param idx
         */
        moveUp(idx) {
            if (this.isMovingLoading) return;

            let list = this.list;
            let that = this;

            if (idx > 0) {
                this.updateSequences(() => {
                    let tmp = list[idx];
                    list[idx] = list[idx - 1];
                    list[idx - 1] = tmp;

                    // this.list = list.slice(0);
                    // this.list = list.concat();
                    [...that.list] = list;
                });
            }
        },

        /**
         * 向下移动。
         * @param idx
         */
        moveDown(idx) {
            if (this.isMovingLoading) return;

            let list = this.list;
            let that = this;

            if (idx < list.length - 1) {
                this.updateSequences(() => {
                    let tmp = list[idx];
                    list[idx] = list[idx + 1];
                    list[idx + 1] = tmp;

                    [...that.list] = list;
                });
            }
        },

        /**
         * 打开切换选择器。
         * @param row
         */
        insertTo(row) {
            this.$emit('show-transfer-box', row);
        },

        /**
         * 删除。
         * @param idx
         */
        deleteItem(idx) {
            let list = this.list;
            let item = list[idx];

            iview.Modal.confirm({
                title: 'Confirm',
                content: 'Are you sure to delete"' + item.node_title + '"?',
                closable: true,
                onOk: () => {
                    httpPost('delete_node_subject', {id: item.id}).then(data => {
                        if (data) {
                            iview.Message.success('Deleted!');
                            list.splice(idx, 1);
                        }
                    }).catch(response => {
                        console.log(response);
                        iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                        if (-57 === response.code) {
                            this.$emit('show-login-box');
                        }
                    });
                }
            });
        },

        /**
         * 改变排序时，同步到服务器。
         */
        updateSequences(fn, fn_args) {
            if (this.isMovingLoading) return;

            this.isMovingLoading = true;

            if (typeof fn === 'function') fn(fn_args);

            let sequences = this.list.map(e => {
                return e.id;
            });

            httpPost('set_node_subjects_sequence', sequences).then(data => {
                if (data) iview.Message.success('Success!');

                this.isMovingLoading = false;
            }).catch(response => {
                console.log(response);
                iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                if (-57 === response.code) {
                    this.$emit('show-login-box');
                }

                this.isMovingLoading = false;
            });
        }
    },
    mounted() {
        this.loadNodesList(0);
    }
};

const app = new Vue({
    el: '#main-box',
    data: {
        isShowLoginBox: false,
        isShowTransferBox: false,
        isShowEditor: false,
        isShowEditorOKBtnLoading: false,
        isShowEditorLoadingSpin: false,

        insertToParentId: 0,
        insertSelectId: 0,
        subjectsEditorId: '',

        loginFormData: {user: '', pass: ''},
        loginFormRule: {
            user: [{required: true, message: 'Please fill in the user name.', trigger: 'blur'}],
            pass: [{required: true, message: 'Please fill in the password.', trigger: 'blur'}]
        },

        nodesTree: [{id: 0, title: 'Main', loading: false, selected: false, children: []}],

        editorFormData: {
            id: 0,
            node_title: '',
            sequence: 0,
            parent_node_id: 0,
            is_cq: 0,
            is_saq: 0,
            subjects: ''
        },
        editorFormRule: {
            node_title: [{required: true, message: 'Please fill in the node title.', trigger: 'blur'}],
        },

        curId: 0,
        curSequence: 0,
        curParentNodeId: 0
    },
    methods: {
        /**
         * 显示登录框。
         */
        handleShowLoginBox() {
            this.isShowLoginBox = true;
        },

        /**
         * 发送登录请求。
         */
        handleLogin() {
            this.$refs.loginForm.validate(valid => {
                if (valid) {
                    httpPost('login', this.loginFormData).then(data => {
                        this.isShowLoginBox = false;
                    }).catch(response => {
                        console.log(response);
                        alert(response.msg);
                    });
                }
            });
        },

        /**
         * 点击添加新项目按钮。
         */
        handleAddNew() {
            // 打开编辑器时清空富文本输入框内容，同时重置表单提示。
            // 对整个表单进行重置，将所有字段值重置为空并移除校验结果。
            this.editorFormData.id = 0;
            this.editorFormData.sequence = 0;
            this.editorFormData.parent_node_id = 0;
            this.$refs.editorForm.resetFields();
            this.setTinyMCEContent('');

            this.isShowEditor = true;
        },

        /**
         * 清空编辑器内容。
         */
        handleOnClearSubjects() {
            this.setTinyMCEContent('');
        },

        /**
         * 节点编辑框 - 点击取消按钮。
         */
        handleOnCancelToAddNew() {
            this.isShowEditor = false;
            this.destroyTinyMCE();
            this.initTinyMCE();
        },

        /**
         * 节点编辑框 - 点击确定 - 提交内容。
         */
        handleOnSubmitToAddNew() {
            this.$refs.editorForm.validate(valid => {
                if (valid) {
                    this.isShowEditorOKBtnLoading = true;
                    let formData = this.editorFormData;

                    formData.subjects = this.getTinyMCEContent();

                    let fn = '';

                    if (parseInt(formData.id) > 0) {
                        fn = 'update_node_subject';
                    } else {
                        fn = 'create_node_subject';
                        formData.sequence = this.curSequence;
                        formData.parent_node_id = this.curId;
                    }

                    httpPost(fn, formData).then(data => {
                        if (parseInt(data) > 0) {
                            this.isShowEditorOKBtnLoading = false;
                            this.isShowEditor = false;
                            this.destroyTinyMCE();
                            this.initTinyMCE();
                        }

                        this.$refs.nodesList.refreshList();
                    }).catch(response => {
                        console.log(response);
                        iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                        if (-57 === response.code) {
                            this.isShowLoginBox = true;
                        }

                        this.isShowEditorOKBtnLoading = false;
                    });
                }
            });
        },

        /**
         * 当列表内容变动后更新当前的节点数据。
         * @param params
         */
        handleOnRefreshList(params) {
            this.curId = params.id;
            this.curSequence = params.cur_sequence;
            this.curParentNodeId = params.parent_node_id;
        },

        /**
         * 点击编辑按钮打开对应节点，获取节点内容并进行编辑。
         * @param params
         */
        handleOpenEditor(params) {
            this.isShowEditor = true;
            this.isShowEditorLoadingSpin = true;
            this.$refs.editorForm.resetFields();
            this.setTinyMCEContent('');
            httpPost('get_node_subject', params).then(data => {
                this.editorFormData = data;

                this.setTinyMCEContent(data.subjects);

                this.isShowEditorLoadingSpin = false;
            }).catch(response => {
                console.log(response);
                iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                if (-57 === response.code) {
                    this.isShowLoginBox = true;
                }
            });
        },

        /**
         * 打开切换选择器。
         * @param params
         */
        handleShowTransferBox(params) {
            let parentNodeId = this.insertToParentId = params.parent_node_id;
            this.insertSelectId = params.id;
            this.nodesTree[0].selected = 0 === parentNodeId;
            this.isShowTransferBox = true;
        },

        /**
         * 切换选择器 - 异步加载数据。
         * @param item
         * @param cb
         */
        handleLoadNodesTree(item, cb) {
            let that = this;

            httpPost('get_nodes', {id: item.id}).then(data => {
                if (data.sub_nodes) {
                    let list = data.sub_nodes.map(e => {
                        return {
                            id: e.id,
                            title: e.node_title,
                            loading: false,
                            selected: that.insertToParentId === e.id,
                            children: []
                        };
                    });

                    cb(list);
                } else {
                    cb([{title: '[Nothing]', disabled: true}]);
                }
            }).catch(response => {
                console.log(response);
                iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                if (-57 === response.code) {
                    this.isShowLoginBox = true;
                }
            });
        },

        /**
         * 切换选择器 - 选择父节点。
         * @param selections
         * @param item
         */
        handleOnSelectChange(selections, item) {
            if (this.insertSelectId === item.id) {
                iview.Message.error('Can\'t Choose itself');
                this.insertSelectId = false;
                item.selected = false;
            } else {
                this.insertToParentId = selections.length > 0 ? item.id : false;
            }
        },

        /**
         * 点击确认键提交修改。
         */
        handleOnInsertOk() {
            if (false === this.insertToParentId) {
                iview.Modal.error({title: 'Error', content: '<p>You must select one.</p>'});
                return;
            }

            httpPost('update_node_parent_id', {
                id: this.insertSelectId,
                parent_node_id: this.insertToParentId
            }).then(data => {
                this.$refs.nodesList.refreshList(this.insertToParentId);
                this.isShowTransferBox = false;
                this.handleOnInsertCancel();
            }).catch(response => {
                console.log(response);
                iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                if (-57 === response.code) {
                    this.isShowLoginBox = true;
                }
            });
        },

        /**
         * 关闭切换选择器后的回调。
         */
        handleOnInsertCancel() {
            this.nodesTree = [{id: 0, title: 'Main', loading: false, selected: false, children: []}];
        },


        /*
         ***************************************************************************************************************
         */

        /**
         * 初始化富文本编辑器实例。
         */
        initTinyMCE() {
            if (typeof tinymce === 'object' && tinymce.get('subjects-editor')) {
                iview.Message.error('TinyMCE init Error, Please refresh this page. [initTinyMCE]');
            } else {
                // let subjectsEditorId = this.subjectsEditorId = 'subjects-editor-' + +new Date();

                let that = this;

                tinymce.init({
                    selector: 'textarea#subjects-editor',
                    // selector: `textarea#${subjectsEditorId}`,
                    plugins: plugins,
                    menubar: 'file edit insert view format tools table',
                    toolbar: toolbar,

                    // content_css: 'css/content.css',
                    // content_css: ["//fonts.googleapis.com/css?family=Lato|Lobster|Noto+Serif|Permanent+Marker|Raleway|Roboto|Source+Code+Pro"],

                    // theme: 'modern',
                    // inline: true,
                    mobile: {theme: 'mobile'},

                    // width: 600,
                    height: 360,
                    // max_height: 500,
                    // max_width: 500,
                    min_height: 240,
                    // min_width: 400,

                    body_class: 'panel-body ',

                    // readonly: true,

                    object_resizing: false,

                    // statusbar: false,

                    image_advtab: true,
                    // without images_upload_url set, Upload tab won't show up
                    images_upload_url: 'upload.php',
                    images_upload_base_path: '',
                    images_upload_credentials: '',
                    imagetools_toolbar: "rotateleft rotateright | flipv fliph | editimage imageoptions",
                    // override default upload handler to simulate successful upload
                    images_upload_handler: function (blobInfo, success, failure) {
                        let xhr, formData, flag = 'file';

                        xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;
                        xhr.open('POST', BASE_FACT + '?fn=image_upload');
                        // xhr.setRequestHeader('Content-Type', 'multipart/form-data');

                        xhr.onload = function () {
                            let response;

                            if (xhr.status !== 200) {
                                failure('HTTP Error[' + xhr.status + ']: ' + xhr.statusText);
                                return;
                            }

                            response = JSON.parse(xhr.responseText);

                            if (!response) {
                                console.log(xhr.responseText);
                                failure('Invalid JSON: ' + xhr.responseText);
                                return;
                            }

                            // 未登录的情况。
                            if (-57 === response.code) {
                                failure(response.msg);
                                that.isShowLoginBox = true;
                                return;
                            }

                            if (1 === response.code) {
                                let data = response.data;
                                let res = data.results[flag];

                                if (res.error) failure(res.error);
                                else success(data.path + '/' + res.file_name);
                            } else {
                                console.log(response);
                                failure(response.msg);
                            }
                        };

                        formData = new FormData();
                        formData.append(flag, blobInfo.blob(), blobInfo.filename());

                        xhr.send(formData);
                    },
                    // powerpaste_allow_local_images: !0,

                    browser_spellcheck: true,
                    // contextmenu: true,
                    // spellchecker_rpc_url: 'spellchecker.php'

                    // schema: 'html5',

                    // language: 'zh_CN', // 'en_US'
                    // directionality: 'rtl', // 'ltr', 'rtl'

                    // br_in_pre: true,

                    // custom_undo_redo_levels: 10,

                    // autosave_ask_before_unload: true,
                    // autosave_interval: "30s",
                    // autosave_prefix: "tinymce-autosave-{path}{query}-{id}-",
                    // autosave_restore_when_empty: false,
                    // autosave_retention: "20m",

                    // save_enablewhendirty: true,
                    save_oncancelcallback: function () {
                        console.log('Save canceled');
                    },
                    save_onsavecallback: function (editor) {
                        let id = that.editorFormData.id;
                        let content = editor.getContent();

                        if (id > 0) {
                            that.isShowEditorLoadingSpin = true;
                            iview.Message.info('Saving...');

                            httpPost('update_node_subject_content', {id, subjects: content}).then(data => {
                                that.isShowEditorLoadingSpin = false;
                                iview.Message.success('Saved!');
                            }).catch(response => {
                                console.log(response);
                                iview.Modal.error({title: 'Error', content: '<p>' + response.msg + '</p>'});

                                if (-57 === response.code) {
                                    that.isShowLoginBox = true;
                                }

                                that.isShowEditorLoadingSpin = false;
                            });
                        }
                    },

                    end_container_on_empty_block: true,

                    // nowrap: true,

                    // object_resizing: true,

                    // typeahead_urls: true,

                    powerpaste_word_import: 'clean',

                    code_dialog_height: 450,
                    code_dialog_width: 1000,
                    advlist_bullet_styles: 'square',
                    advlist_number_styles: 'default',
                    imagetools_cors_hosts: ['www.tinymce.com', 'codepen.io'],
                    default_link_target: '_blank',
                    link_title: false,
                    nonbreaking_force_tab: true, // inserting nonbreaking space &nbsp; need Nonbreaking Space Plugin
                    init_instance_callback: editor => {
                        console.info('init_instance_callback OK.');
                        console.info(editor);

                        /*
                        // 实现 Ctrl + S 保存文件，版本 1 。
                        editor.on('KeyDown', (e) => {
                            let num = e.which ? e.which : e.keyCode;

                            if (e.ctrlKey && num === 83 && that.editorFormData.id > 0) {
                                iview.Message.info('Saving...');

                                setTimeout(() => {
                                    let content = that.getTinyMCEContent();

                                    iview.Message.success('Saved!');
                                }, 1500);

                                e.preventDefault();
                            }
                        });
                        */

                        /*
                        // 实现 Ctrl + S 保存文件，版本 2 。
                        let doc = editor.getBody();

                        addEvent(doc, 'keydown', function (e) {
                            let num = e.which ? e.which : e.keyCode;

                            if (e.ctrlKey && num === 83 && that.editorFormData.id > 0) {
                                iview.Message.info('Saving...');

                                setTimeout(() => {
                                    let content = that.getTinyMCEContent();

                                    iview.Message.success('Saved!');
                                }, 1500);

                                e.preventDefault();
                            }
                        });
                        */

                        /*
                        // 改变内容的情况下修改状态。
                        editor.setContent(_this.value);
                        editor.on('NodeChange Change KeyUp SetContent', () => {
                            this.hasChange = true;
                            this.$emit('input', editor.getContent());
                        });
                        */


                        /**
                         * 给对象添加事件，为了用并兼容所有浏览器。
                         * @param target
                         * @param eventType
                         * @param func
                         * @returns {addEvent}
                         function addEvent(target, eventType, func) {
                            if (target.attachEvent) {
                                target.attachEvent("on" + eventType, func);
                            } else if (target.addEventListener) {
                                target.addEventListener(eventType, func, false);
                            }

                            return this;
                        }
                         */

                    },
                    setup(editor) {
                        editor.on('FullscreenStateChanged', (e) => {
                            // _this.fullscreen = e.state
                        })
                    },
                    images_dataimg_filter(img) {

                    },

                    // font_formats: "Aileron=aileron, sans-serif;Helvetica=helvetica, arial;Lato=lato, sans-serif;Lobster=lobster, chicago, serif;Noto Serif=noto serif, serif;Permanent Marker=permanent marker, sans-serif;Raleway=raleway, sans-serif;Roboto=roboto, sans-serif;Source Code Pro=source code pro, monospace"

                    // 补充设置字体大小的功能。
                    // fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt'

                    // 插入代码块所支持的类型。
                    /*
                    codesample_languages: [
                        {text: 'HTML/XML', value: 'markup'},
                        {text: 'JavaScript', value: 'javascript'},
                        {text: 'CSS', value: 'css'},
                        {text: 'PHP', value: 'php'},
                        {text: 'Ruby', value: 'ruby'},
                        {text: 'Python', value: 'python'},
                        {text: 'Java', value: 'java'},
                        {text: 'C', value: 'c'},
                        {text: 'C#', value: 'csharp'},
                        {text: 'C++', value: 'cpp'}
                    ],
                    */

                });
            }
        },

        /**
         * 销毁富文本编辑器实例。
         */
        destroyTinyMCE() {
            if (typeof tinymce === 'object' && tinymce.get('subjects-editor')) {
                tinymce.get('subjects-editor').destroy();
            } else {
                iview.Message.error('TinyMCE init Error, Please refresh this page. [destroyTinyMCE]');
            }
        },

        /**
         * 设置富文本编辑器的内容。
         */
        setTinyMCEContent(content = '') {
            if (typeof tinymce === 'object' && tinymce.get('subjects-editor')) {
                tinymce.get('subjects-editor').setContent(content);
            } else {
                iview.Message.error('TinyMCE init Error, Please refresh this page. [setTinyMCEContent]');
            }
        },

        /**
         * 获取富文本编辑器内容。
         */
        getTinyMCEContent() {
            let content = '';

            if (typeof tinymce === 'object' && tinymce.get('subjects-editor')) {
                content = tinymce.get('subjects-editor').getContent();
            } else {
                iview.Message.error('TinyMCE init Error, Please refresh this page. [getTinyMCEContent]');
            }

            return content;
        }
    },
    mounted() {
        // 解决页面加载初期显示未被正确渲染的元素。
        document.getElementById('node-editor').style.display = 'block';
        this.initTinyMCE();
    },
    components: {PbxMain}
});


