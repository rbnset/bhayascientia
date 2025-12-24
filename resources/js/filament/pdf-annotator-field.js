import * as pdfjsLib from 'pdfjs-dist';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
    'pdfjs-dist/build/pdf.worker.min.mjs',
    import.meta.url
).toString();

window.pdfAnnotatorField = function ({ state, fileUrl }) {
    return {
        state,
        fileUrl,

        pdf: null,
        page: 1,
        pageCount: 1,

        scale: 1.25,
        viewportWidth: 800,
        viewportHeight: 1000,

        tool: 'pan',
        isDragging: false,
        dragStart: null,

        init() {
            if (!this.state) this.state = { version: 1, byPage: {} };
            if (!this.state.byPage) this.state.byPage = {};

            this.$watch('fileUrl', async (val) => {
                if (!val) return;
                await this.loadPdf(val);
            });

            if (this.fileUrl) {
                this.loadPdf(this.fileUrl);
            }

            this.bindOverlayEvents();
        },

        setTool(t) {
            this.tool = t;
        },

        async loadPdf(url) {
            this.pdf = await pdfjsLib.getDocument(url).promise;
            this.pageCount = this.pdf.numPages;
            this.page = 1;
            await this.renderPage();
        },

        async renderPage() {
            if (!this.pdf) return;

            const pageObj = await this.pdf.getPage(this.page);
            const viewport = pageObj.getViewport({ scale: this.scale });

            this.viewportWidth = Math.ceil(viewport.width);
            this.viewportHeight = Math.ceil(viewport.height);

            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');

            canvas.width = this.viewportWidth;
            canvas.height = this.viewportHeight;

            await pageObj.render({ canvasContext: ctx, viewport }).promise;

            this.renderAnnotationsOverlay();
        },

        prevPage() {
            if (this.page <= 1) return;
            this.page--;
            this.renderPage();
        },

        nextPage() {
            if (this.page >= this.pageCount) return;
            this.page++;
            this.renderPage();
        },

        getPageAnnotations() {
            const key = String(this.page);
            if (!this.state.byPage[key]) this.state.byPage[key] = [];
            return this.state.byPage[key];
        },

        pushAnnotation(ann) {
            const list = this.getPageAnnotations();
            list.push(ann);
            this.state = { ...this.state }; // trigger entangle update
            this.renderAnnotationsOverlay();
        },

        undo() {
            const list = this.getPageAnnotations();
            list.pop();
            this.state = { ...this.state };
            this.renderAnnotationsOverlay();
        },

        clearAll() {
            const key = String(this.page);
            this.state.byPage[key] = [];
            this.state = { ...this.state };
            this.renderAnnotationsOverlay();
        },

        renderAnnotationsOverlay() {
            const overlay = this.$refs.overlay;
            overlay.innerHTML = '';

            const list = this.getPageAnnotations();

            for (const ann of list) {
                if (ann.type === 'highlight') {
                    const el = document.createElement('div');
                    el.style.position = 'absolute';
                    el.style.left = ann.x + 'px';
                    el.style.top = ann.y + 'px';
                    el.style.width = ann.w + 'px';
                    el.style.height = ann.h + 'px';
                    el.style.background = 'rgba(255, 235, 59, 0.45)';
                    el.style.border = '1px solid rgba(251, 191, 36, 0.9)';
                    overlay.appendChild(el);
                }

                if (ann.type === 'note') {
                    const el = document.createElement('div');
                    el.style.position = 'absolute';
                    el.style.left = ann.x + 'px';
                    el.style.top = ann.y + 'px';
                    el.style.maxWidth = '240px';
                    el.style.background = 'rgba(59, 130, 246, 0.12)';
                    el.style.border = '1px solid rgba(59, 130, 246, 0.6)';
                    el.style.padding = '6px 8px';
                    el.style.fontSize = '12px';
                    el.style.borderRadius = '8px';
                    el.textContent = ann.text;
                    overlay.appendChild(el);
                }
            }
        },

        bindOverlayEvents() {
            const overlay = this.$refs.overlay;

            overlay.addEventListener('mousedown', (e) => {
                if (this.tool !== 'highlight') return;

                this.isDragging = true;
                const rect = overlay.getBoundingClientRect();
                this.dragStart = { x: e.clientX - rect.left, y: e.clientY - rect.top };

                this.tempBox = document.createElement('div');
                this.tempBox.style.position = 'absolute';
                this.tempBox.style.background = 'rgba(255, 235, 59, 0.25)';
                this.tempBox.style.border = '1px dashed rgba(251, 191, 36, 0.9)';
                overlay.appendChild(this.tempBox);
            });

            overlay.addEventListener('mousemove', (e) => {
                if (!this.isDragging || this.tool !== 'highlight') return;

                const rect = overlay.getBoundingClientRect();
                const x2 = e.clientX - rect.left;
                const y2 = e.clientY - rect.top;

                const x = Math.min(this.dragStart.x, x2);
                const y = Math.min(this.dragStart.y, y2);
                const w = Math.abs(this.dragStart.x - x2);
                const h = Math.abs(this.dragStart.y - y2);

                Object.assign(this.tempBox.style, {
                    left: x + 'px',
                    top: y + 'px',
                    width: w + 'px',
                    height: h + 'px',
                });
            });

            overlay.addEventListener('mouseup', (e) => {
                if (!this.isDragging || this.tool !== 'highlight') return;

                this.isDragging = false;

                const x = parseFloat(this.tempBox.style.left);
                const y = parseFloat(this.tempBox.style.top);
                const w = parseFloat(this.tempBox.style.width);
                const h = parseFloat(this.tempBox.style.height);

                this.tempBox.remove();
                this.tempBox = null;

                // ignore small drags
                if (w < 6 || h < 6) return;

                this.pushAnnotation({
                    type: 'highlight',
                    x, y, w, h,
                    createdAt: new Date().toISOString(),
                });
            });

            overlay.addEventListener('click', (e) => {
                if (this.tool !== 'note') return;

                const rect = overlay.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const text = prompt('Comment / note:');
                if (!text) return;

                this.pushAnnotation({
                    type: 'note',
                    x, y,
                    text,
                    createdAt: new Date().toISOString(),
                });
            });
        },
    };
};
