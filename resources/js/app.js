import './bootstrap';
import './editor'; // ← add this line
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { initTableOfContents, initActiveHeadingTracking, smoothScrollToHeading } from './toc';

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('toc-wrapper')) {
        const headings = initTableOfContents();

        if (headings && headings.length > 0) {
            initActiveHeadingTracking(headings);
        }

        document.addEventListener('click', function (e) {
            const tocLink = e.target.closest('[data-toc-link]');
            if (!tocLink) return;
            e.preventDefault();
            const headingId = tocLink.dataset.tocLink;
            smoothScrollToHeading(headingId);
        });
    }
});
