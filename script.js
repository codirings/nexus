// Smooth Scroll Implementation
document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scroll for all anchor links
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Navbar background on scroll
    const navbar = document.querySelector('.navbar');
    let lastScroll = 0;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            navbar.style.backgroundColor = 'rgba(0, 0, 0, 0.95)';
            navbar.style.backdropFilter = 'blur(10px)';
        } else {
            navbar.style.backgroundColor = '#000000';
            navbar.style.backdropFilter = 'none';
        }
        
        lastScroll = currentScroll;
    });
    
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all sections
    const sections = document.querySelectorAll('.about-section, .categories-section, .placeholder-item');
    sections.forEach(section => {
        observer.observe(section);
    });
    
    // Add parallax effect to hero section
    const hero = document.querySelector('.hero');
    
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallaxSpeed = 0.5;
        
        if (hero) {
            hero.style.transform = `translateY(${scrolled * parallaxSpeed}px)`;
        }
    });
    
    // Cursor trail effect (optional enhancement)
    let mouseX = 0;
    let mouseY = 0;
    let cursorX = 0;
    let cursorY = 0;
    
    document.addEventListener('mousemove', function(e) {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });
    
    // Enhanced hover effects for placeholder boxes
    const placeholders = document.querySelectorAll('.placeholder-box');
    
    placeholders.forEach(placeholder => {
        placeholder.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05) translateY(-10px)';
        });
        
        placeholder.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) translateY(0)';
        });
    });
    
    // Smooth page transitions
    window.addEventListener('beforeunload', function() {
        document.body.style.opacity = '0';
        document.body.style.transition = 'opacity 0.3s ease';
    });
    
    // Page load animation
    window.addEventListener('load', function() {
        document.body.style.opacity = '1';
        document.body.style.transition = 'opacity 0.5s ease';
    });
    
    // Add active state to nav links based on current page
    const currentLocation = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (linkHref === currentLocation) {
            link.style.color = '#0099ff';
        }
    });
    
    // Lazy loading for images (when you add actual product images)
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
    
    // Custom cursor (optional)
    const cursor = document.createElement('div');
    cursor.className = 'custom-cursor';
    document.body.appendChild(cursor);
    
    document.addEventListener('mousemove', (e) => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
    });
    
    // Add CSS for custom cursor
    const style = document.createElement('style');
    style.textContent = `
        .custom-cursor {
            width: 20px;
            height: 20px;
            border: 2px solid #0099ff;
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.2s ease, opacity 0.2s ease;
            opacity: 0;
            transform: translate(-50%, -50%);
        }
        
        body:hover .custom-cursor {
            opacity: 0.5;
        }
    `;
    document.head.appendChild(style);
    
    console.log('NEXUS website initialized successfully');
});

// Smooth scroll for the entire page
(function() {
    let scrolling = false;
    
    window.addEventListener('wheel', function(e) {
        if (scrolling) return;
        
        scrolling = true;
        
        setTimeout(function() {
            scrolling = false;
        }, 50);
    }, { passive: true });
})();

// Added Animations Here LOL

document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function () {

        // Get product info
        const card = this.closest('.product-card');
        const name = card.querySelector('h3').innerText;
        const price = card.querySelector('p').innerText;

        // Store in localStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        cart.push({ name, price, quantity: 1 });

        localStorage.setItem('cart', JSON.stringify(cart));

        // Animation feedback
        this.innerText = "ADDED ✓";
        this.style.background = "#0099ff";

        setTimeout(() => {
            this.innerText = "ADD TO CART";
            this.style.background = "black";
        }, 1200);
    });
});


//Hero Parallax Animation 

// ADVANCED PARALLAX
