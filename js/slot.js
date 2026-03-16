function selectSlot(event, slot) {
    document.getElementById('selectedSlot').value = slot;
    document.querySelectorAll('.slot').forEach(btn =>
        btn.classList.remove('selected')
    );
    event.target.classList.add('selected');
    document.querySelector('.confirm-btn').disabled = false;
}