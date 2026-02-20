import Alpine from 'alpinejs';

Alpine.data('imageCompressor', () => ({
    compressing: false,

    async compressFiles(event) {
        const config = window.imageOptimization || {};
        if (!config.enabled) return;

        const input = event.target;
        const files = Array.from(input.files);
        if (files.length === 0) return;

        const maxDim = Math.max(config.maxWidth || 1920, config.maxHeight || 1920);
        const hasImages = files.some(f => f.type.startsWith('image/'));
        if (!hasImages) return;

        this.compressing = true;

        try {
            const processed = await Promise.all(files.map(file => {
                if (!file.type.startsWith('image/')) {
                    return Promise.resolve(file);
                }
                return this._compressImage(file, maxDim);
            }));

            const dt = new DataTransfer();
            processed.forEach(f => dt.items.add(f));
            input.files = dt.files;
        } catch (err) {
            console.warn('Image compression failed, using originals:', err);
        } finally {
            this.compressing = false;
        }
    },

    _compressImage(file, maxDim) {
        return new Promise((resolve) => {
            const img = new Image();
            const url = URL.createObjectURL(file);

            img.onload = () => {
                URL.revokeObjectURL(url);

                let { width, height } = img;

                // Scale down if exceeds max dimensions
                if (width > maxDim || height > maxDim) {
                    const ratio = Math.min(maxDim / width, maxDim / height);
                    width = Math.round(width * ratio);
                    height = Math.round(height * ratio);
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (!blob || blob.size >= file.size) {
                            resolve(file); // Original was smaller, keep it
                        } else {
                            resolve(new File([blob], file.name, {
                                type: blob.type,
                                lastModified: file.lastModified,
                            }));
                        }
                    },
                    'image/jpeg',
                    0.85
                );
            };

            img.onerror = () => {
                URL.revokeObjectURL(url);
                resolve(file); // Fall back to original
            };

            img.src = url;
        });
    },
}));
