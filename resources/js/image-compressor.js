import imageCompression from 'browser-image-compression';
import Alpine from 'alpinejs';

Alpine.data('imageCompressor', () => ({
    compressing: false,

    async compressFiles(event) {
        const config = window.imageOptimization || {};
        if (!config.enabled) return;

        const input = event.target;
        const files = Array.from(input.files);

        if (files.length === 0) return;

        const imageFiles = files.filter(f => f.type.startsWith('image/'));
        const nonImageFiles = files.filter(f => !f.type.startsWith('image/'));

        if (imageFiles.length === 0) return;

        this.compressing = true;

        try {
            const options = {
                maxSizeMB: 1,
                maxWidthOrHeight: Math.max(config.maxWidth || 1920, config.maxHeight || 1920),
                useWebWorker: true,
            };

            const compressed = await Promise.all(
                imageFiles.map(file => imageCompression(file, options).then(blob => {
                    // Preserve original filename
                    return new File([blob], file.name, { type: blob.type, lastModified: file.lastModified });
                }))
            );

            const dt = new DataTransfer();
            [...compressed, ...nonImageFiles].forEach(f => dt.items.add(f));
            input.files = dt.files;
        } catch (err) {
            console.warn('Image compression failed, using originals:', err);
        } finally {
            this.compressing = false;
        }
    },
}));
