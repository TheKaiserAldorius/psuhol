document.addEventListener('DOMContentLoaded', function() {

    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav');
    
    menuToggle.addEventListener('click', function() {
        this.classList.toggle('active');
        nav.classList.toggle('active');
    });
    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.getAttribute('href') === '#') return;
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                menuToggle.classList.remove('active');
                nav.classList.remove('active');
            }
        });
    });
    
    const stats = document.querySelectorAll('.stat__number');
    const statsSection = document.querySelector('.about');
    
    function animateStats() {
        const rect = statsSection.getBoundingClientRect();
        const isVisible = (rect.top <= window.innerHeight / 2) && (rect.bottom >= window.innerHeight / 2);
        
        if (isVisible) {
            stats.forEach(stat => {
                const target = parseInt(stat.getAttribute('data-count'));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        clearInterval(timer);
                        current = target;
                    }
                    stat.textContent = Math.floor(current);
                }, 16);
            });
            
        
            window.removeEventListener('scroll', animateStats);
        }
    }
    
    window.addEventListener('scroll', animateStats);
   
    const cases = document.querySelectorAll('.case');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');
    const dotsContainer = document.querySelector('.slider-dots');
    let currentIndex = 0;
    

    cases.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('slider-dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
    });
    
    const dots = document.querySelectorAll('.slider-dot');
    
    function updateSlider() {
        cases.forEach((caseItem, index) => {
            caseItem.classList.remove('active');
            dots[index].classList.remove('active');
        });
        
        cases[currentIndex].classList.add('active');
        dots[currentIndex].classList.add('active');
    }
    
    function goToSlide(index) {
        currentIndex = index;
        updateSlider();
        resetSlideInterval();
    }
    
    function nextSlide() {
        currentIndex = (currentIndex + 1) % cases.length;
        updateSlider();
        resetSlideInterval();
    }
    
    function prevSlide() {
        currentIndex = (currentIndex - 1 + cases.length) % cases.length;
        updateSlider();
        resetSlideInterval();
    }
    
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);

    let slideInterval = setInterval(nextSlide, 5000);
    
    const sliderContainer = document.querySelector('.cases-slider');
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });
        
        sliderContainer.addEventListener('mouseleave', () => {
            slideInterval = setInterval(nextSlide, 5000);
        });
    }
    
    function resetSlideInterval() {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    }
const form = document.getElementById('appointment-form');
if (form) {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        
        try {
      
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';
            
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const text = await response.text();
            let result = {};
            if (text) {
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Неверный формат ответа от сервера');
                }
            }

            showAlert('success', result.message || 'Заявка успешно отправлена!');
            form.reset();
            
        } catch (error) {
            console.error('Form submission error:', error);
            showAlert('error', error.message || 'Произошла ошибка при отправке формы');
        } finally {
         
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });
}

    function showAlert(type, message) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;

        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.padding = '15px 25px';
        alert.style.borderRadius = '5px';
        alert.style.color = 'white';
        alert.style.fontWeight = '500';
        alert.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        alert.style.zIndex = '10000';
        alert.style.animation = 'fadeIn 0.3s ease-out';
        
        if (type === 'success') {
            alert.style.background = '#50c878';
        } else {
            alert.style.background = '#ff6b6b';
        }
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    }

    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-20px); }
        }
    `;
    document.head.appendChild(style);
});