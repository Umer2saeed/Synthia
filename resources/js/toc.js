export function extractHeadings(contentElement) {
    const headingElements = contentElement.querySelectorAll('h2, h3, h4');
    const headings        = [];
    const usedIds         = {};

    headingElements.forEach(function (element) {
        const text   = element.textContent.trim();
        let   baseId = text
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/^-+|-+$/g, '');

        let id = baseId;
        if (usedIds[baseId]) {
            usedIds[baseId]++;
            id = baseId + '-' + usedIds[baseId];
        } else {
            usedIds[baseId] = 1;
        }

        if (!element.id) {
            element.id = id;
        } else {
            id = element.id;
        }

        headings.push({
            id:      id,
            text:    text,
            level:   parseInt(element.tagName.charAt(1)),
            element: element,
        });
    });

    return headings;
}

/*
|--------------------------------------------------------------------------
| buildTocItems() — Build TOC using real DOM elements, not HTML strings
|--------------------------------------------------------------------------
| WHY DOM elements instead of innerHTML with HTML strings?
| When you set innerHTML with a long HTML string containing Tailwind classes
| that have colons (hover:, dark:, focus:), some browser parsers
| misinterpret the colon as part of an attribute selector and
| render the raw string as text content instead of HTML.
|
| Building DOM elements programmatically avoids this entirely.
| We create each element, set properties directly, and append to the DOM.
*/
function buildTocItems(headings, container) {
    // Clear existing content
    container.innerHTML = '';

    const ul = document.createElement('ul');
    ul.className = 'space-y-1';

    headings.forEach(function (heading) {
        const li = document.createElement('li');

        // Set indentation based on heading level
        if (heading.level === 3) {
            li.className = 'pl-4';
        } else if (heading.level === 4) {
            li.className = 'pl-8';
        }

        const a = document.createElement('a');
        a.href = '#' + heading.id;

        // Store the heading ID for active tracking and click handling
        a.dataset.tocLink = heading.id;

        // Set text content (NOT innerHTML — avoids XSS)
        a.textContent = heading.text;

        /*
        | Set classes individually rather than as a long string.
        | This is more reliable across browsers than className with
        | complex Tailwind utility classes containing colons.
        */
        a.classList.add(
            'toc-link',
            'block',
            'py-1',
            'px-2',
            'rounded-lg',
            'transition-colors',
            'duration-150',
            'border-l-2',
            'border-transparent',
            'no-underline'
        );

        // Size and weight based on level
        if (heading.level === 2) {
            a.classList.add('text-sm', 'font-medium');
        } else {
            a.classList.add('text-xs', 'font-normal');
        }

        // Default color classes
        a.classList.add('text-gray-600');

        li.appendChild(a);
        ul.appendChild(li);
    });

    container.appendChild(ul);
}

export function initTableOfContents() {
    const content    = document.getElementById('post-content');
    const tocWrapper = document.getElementById('toc-wrapper');

    if (!content || !tocWrapper) return;

    const headings = extractHeadings(content);

    if (headings.length < 2) {
        tocWrapper.style.display = 'none';
        return;
    }

    // Build TOC using DOM elements — not innerHTML with HTML strings
    const tocList = tocWrapper.querySelector('[data-toc-list]');
    if (tocList) {
        buildTocItems(headings, tocList);
    }

    // Show the wrapper
    tocWrapper.classList.remove('hidden');

    // Update section count badge
    const countBadge = tocWrapper.querySelector('[data-toc-count]');
    if (countBadge) {
        countBadge.textContent = headings.length + ' sections';
    }

    return headings;
}

export function smoothScrollToHeading(headingId) {
    const element = document.getElementById(headingId);
    if (!element) return;

    const navbarHeight = 80;
    const elementTop   = element.getBoundingClientRect().top + window.scrollY;
    const offsetTop    = elementTop - navbarHeight - 16;

    window.scrollTo({
        top:      offsetTop,
        behavior: 'smooth',
    });

    history.pushState(null, null, '#' + headingId);
}

export function initActiveHeadingTracking(headings) {
    if (headings.length === 0) return;

    let activeHeadingId = headings[0].id;

    const observer = new IntersectionObserver(
        function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    activeHeadingId = entry.target.id;
                    highlightTocLink(activeHeadingId);
                }
            });
        },
        {
            rootMargin: '-80px 0px -70% 0px',
            threshold:  0,
        }
    );

    headings.forEach(function (heading) {
        observer.observe(heading.element);
    });

    highlightTocLink(activeHeadingId);
}

function highlightTocLink(activeId) {
    // Remove active styles from all links
    document.querySelectorAll('[data-toc-link]').forEach(function (link) {
        link.style.color           = '';
        link.style.backgroundColor = '';
        link.style.borderLeftColor = 'transparent';
        link.classList.remove('toc-active');
    });

    // Add active style to matching link
    const activeLink = document.querySelector(`[data-toc-link="${activeId}"]`);
    if (activeLink) {
        activeLink.style.color           = '#4f46e5'; // indigo-600
        activeLink.style.backgroundColor = '#eef2ff'; // indigo-50
        activeLink.style.borderLeftColor = '#4f46e5'; // indigo-600
        activeLink.classList.add('toc-active');
    }
}
