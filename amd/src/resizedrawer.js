define([], function() {
    const init = () => {
        const drawer = document.querySelector('.drawer-right');
        const page = document.querySelector('#page.drawers') || document.querySelector('#page');

        if (!drawer || !page) {
            return;
        }

        drawer.style.maxWidth = '700px';

        const handle = document.createElement('div');
        handle.className = 'block-drawer-resize';
        drawer.appendChild(handle);

        handle.addEventListener('pointerdown', (event) => {
            const startX = event.clientX;
            const startWidth = drawer.offsetWidth;
            let pendingWidth = null;
            let rafId = null;

            // Disable CSS transitions for fluid transition
            drawer.style.transition = 'none';
            page.style.transition = 'none';
            handle.setPointerCapture(event.pointerId);

            const resize = (event) => {
                pendingWidth = Math.max(280, Math.min(700, startWidth + startX - event.clientX));
                if (rafId === null) {
                    rafId = requestAnimationFrame(() => {
                        drawer.style.width = `${pendingWidth}px`;
                        page.style.marginRight = `${pendingWidth}px`;
                        rafId = null;
                    });
                }
            };

            const stop = () => {
                document.removeEventListener('pointermove', resize);
                document.removeEventListener('pointerup', stop);
                if (rafId !== null) {
                    cancelAnimationFrame(rafId);
                    rafId = null;
                }
                drawer.style.transition = '';
                page.style.transition = '';
            };

            document.addEventListener('pointermove', resize);
            document.addEventListener('pointerup', stop);
        });

        //reset
        const observer = new MutationObserver(() => {
            if (drawer.classList.contains('hidden') || !document.body.classList.contains('drawer-open-right')) {
                page.style.marginRight = '';
                drawer.style.width = '';
            }
        });
        observer.observe(drawer, {attributes: true, attributeFilter: ['class']});
        observer.observe(document.body, {attributes: true, attributeFilter: ['class']});
    };

    return {init};
});