define([], function() {
    const init = () => {
        const drawer = document.querySelector('.drawer-right');

        if (!drawer) {
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
                const width = startWidth + startX - event.clientX;
                drawer.style.width = `${Math.max(280, Math.min(700, width))}px`;
            };

            const stop = () => {
                document.removeEventListener('pointermove', resize);
                document.removeEventListener('pointerup', stop);
            };

            document.addEventListener('pointermove', resize);
            document.addEventListener('pointerup', stop);
        });
    };

    return {init};
});