const app = new Vue({
    el: '#main-box',
    data: {
        isShowMenu: false,

        parentId: 0,
        curId: 0,

        tempId: 0,

        curNode: {
            id: 0,
            node_title: '',
            parent_node_id: 0,
            is_cq: 0,
            is_saq: 0
        },

        title: '',
        isCQ: false,
        isSAQ: false,
        list: [],
        content: '',
        subNodesList: [],

        isShowLoading1: false,
        isShowLoading2: false,
        isShowLoginBox: false
    },
    computed: {
        curPage() {
            if (this.list.length === 0) return 0;
            let that = this;

            let idx = this.list.findIndex(e => {
                return e.id === that.curId;
            });

            return idx < 0 ? 1 : idx + 1;
        },
        totalPage() {
            return this.list.length;
        }
    },
    watch: {
        content(val, oldVal) {
            if (val) window.scrollTo(0, 0);
        }
    },
    methods: {
        /**
         * 登录成功后的回调。
         */
        handleLoginSuccess() {
            this.isShowLoginBox = false;
        },

        /**
         * 根据父节点 ID 获取父节点标题和子节点列表。
         * @param parentId
         * @param cb
         */
        loadNodesList(parentId, cb) {
            if (this.isShowLoading1) return;
            this.isShowLoading1 = true;

            httpPost('get_nodes', {id: parentId}).then(data => {
                let curNode = data.current_node;
                let list = false === data.sub_nodes ? [] : data.sub_nodes;

                if (false !== data.sub_nodes) {
                    this.curNode = curNode;
                    this.list = list;
                }

                if (typeof cb === 'function') cb(curNode, list);

                this.isShowLoading1 = false;
            }).catch(response => {
                console.log(response);
                alert(response.msg);

                if (-57 === response.code) {
                    this.isShowLoginBox = true;
                }

                this.isShowLoading1 = false;
            });
        },

        /**
         * 加载子节点。
         * @param params
         */
        handleOnLoad(params) {
            this.loadNodesList(params.id, (curNode, list) => {
                this.parentId = curNode.id;

                // 当点击的子节点没有下级的时候直接显示这个节点的内容。
                if (0 === list.length) {
                    this.loadNodeSubjects(curNode.id);
                    this.tempId = this.parentId = curNode.id;
                    this.isShowMenu = false;
                }
            });
        },

        /**
         * 加载节点详细内容。
         * @param id
         */
        loadNodeSubjects(id) {
            if (this.isShowLoading2) return;
            this.isShowLoading2 = true;

            httpPost('get_node_subject', {id, with_sub_nodes: 1}).then(data => {
                this.curId = data.id;
                this.parentId = data.parent_node_id;
                this.title = data.node_title;
                this.isCQ = 1 === parseInt(data.is_cq);
                this.isSAQ = 1 === parseInt(data.is_saq);
                this.content = data.subjects;
                this.subNodesList = false === data.sub_nodes ? [] : data.sub_nodes;

                this.isShowLoading2 = false;
            }).catch(response => {
                console.log(response);
                alert(response.msg);

                if (-57 === response.code) {
                    this.isShowLoginBox = true;
                }

                this.isShowLoading2 = false;
            });
        },

        /**
         * 隐藏菜单时刷新节点详细内容。
         */
        handleOnMenuHide() {
            if (this.parentId !== this.tempId) {
                if (this.list.length > 0) this.loadNodeSubjects(this.list[0].id);
                this.tempId = this.parentId;
            }

            this.isShowMenu = false;
        },

        /**
         * 显示菜单。
         */
        handleOnShowMenu() {
            // this.tempId = this.list[this.curPage - 1].parent_node_id;
            this.tempId = this.parentId;
            this.isShowMenu = true;
        },

        /**
         * 向前翻页。
         */
        handleOnPrev() {
            if (!this.curPage || 1 === this.curPage) return;
            this.loadNodeSubjects(this.list[this.curPage - 2].id);
        },

        /**
         * 向后翻页。
         */
        handleOnNext() {
            if (this.totalPage === this.curPage) return;
            this.loadNodeSubjects(this.list[this.curPage].id);
        },

        /**
         * 返回上一级。
         */
        handleOnUpperLevel() {
            this.loadNodesList(this.curNode.parent_node_id, (curNode, list) => {
                if (list.length > 0) this.loadNodeSubjects(list[0].id);
            });
        },

        /**
         * 点击子菜单加载新节点和节点内容。
         * @param params
         */
        handleLoadSubNodes(params) {
            this.loadNodesList(params.parent_node_id, (curNode, list) => {
                this.parentId = curNode.id;
                this.loadNodeSubjects(params.id);
            });
        }
    },
    mounted() {
        this.loadNodesList(0, (curNode, list) => {
            if (list.length > 0) this.loadNodeSubjects(list[0].id);
        });
    },
    components: {LoginBox, StarLoading, MHeader, HBMenu, SubNodesList}
});