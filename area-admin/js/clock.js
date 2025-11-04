// Reloj Analógico y Digital
function updateClock() {
    const now = new Date();
    
    // Obtener tiempo
    const hours = now.getHours();
    const minutes = now.getMinutes();
    const seconds = now.getSeconds();
    
    // Calcular ángulos para las manecillas
    const secondAngle = (seconds * 6) - 90; // 6 grados por segundo
    const minuteAngle = (minutes * 6 + seconds * 0.1) - 90; // 6 grados por minuto
    const hourAngle = ((hours % 12) * 30 + minutes * 0.5) - 90; // 30 grados por hora
    
    // Aplicar rotación a las manecillas
    const hourHand = document.getElementById('hourHand');
    const minuteHand = document.getElementById('minuteHand');
    const secondHand = document.getElementById('secondHand');
    
    if (hourHand) hourHand.style.transform = `rotate(${hourAngle}deg)`;
    if (minuteHand) minuteHand.style.transform = `rotate(${minuteAngle}deg)`;
    if (secondHand) secondHand.style.transform = `rotate(${secondAngle}deg)`;
    
    // Actualizar reloj digital
    const digitalTime = document.getElementById('digitalTime');
    if (digitalTime) {
        const hoursStr = hours.toString().padStart(2, '0');
        const minutesStr = minutes.toString().padStart(2, '0');
        const secondsStr = seconds.toString().padStart(2, '0');
        digitalTime.textContent = `${hoursStr}:${minutesStr}:${secondsStr}`;
    }
    
    // Actualizar fecha
    const currentDate = document.getElementById('currentDate');
    if (currentDate) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        currentDate.textContent = now.toLocaleDateString('es-ES', options);
    }
}

// Iniciar reloj
if (document.getElementById('hourHand')) {
    updateClock();
    setInterval(updateClock, 1000);
}

// Animación de entrada para las cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.fade-in');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '1';
        }, index * 100);
    });
});
