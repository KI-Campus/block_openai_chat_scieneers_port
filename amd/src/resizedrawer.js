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

            const resize = (event) => {
                const width = Math.max(280, Math.min(700, startWidth + startX - event.clientX));
                drawer.style.width = `${width}px`;
                page.style.marginRight = `${width}px`;
            };

            const stop = () => {
                document.removeEventListener('pointermove', resize);
                document.removeEventListener('pointerup', stop);
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