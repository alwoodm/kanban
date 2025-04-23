import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', function() {
    // Inicjalizacja drag-and-drop dla kart w każdej kolumnie
    const columnsList = document.querySelectorAll('.kanban-cards');
    
    if (columnsList.length === 0) return;
    
    columnsList.forEach(column => {
        new Sortable(column, {
            group: 'cards', // Pozwala na przeciąganie między kolumnami
            animation: 150,
            ghostClass: 'bg-light', // Klasa CSS dla elementu podczas przeciągania
            onEnd: function(evt) {
                const cardId = evt.item.dataset.cardId;
                const newColumnId = evt.to.closest('.kanban-column').dataset.columnId;
                const oldColumnId = evt.from.closest('.kanban-column').dataset.columnId;
                const newIndex = Array.from(evt.to.children).indexOf(evt.item);
                
                // Wyślij żądanie do aktualizacji pozycji karty
                fetch(`/cards/${cardId}/move`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        column_id: newColumnId,
                        order: newIndex + 1, // Indeksy zaczynamy od 1 w bazie danych
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        // Jeśli wystąpił błąd (np. limit kart), przywróć kartę do oryginalnej kolumny
                        evt.from.appendChild(evt.item);
                        return response.json().then(data => {
                            throw new Error(data.error || 'Nie udało się przenieść karty');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Aktualizuj liczniki kart
                    updateColumnCardCount(oldColumnId);
                    if (oldColumnId !== newColumnId) {
                        updateColumnCardCount(newColumnId);
                    }
                })
                .catch(error => {
                    console.error('Błąd:', error);
                    alert(error.message);
                });
            }
        });
    });
    
    // Funkcja aktualizacji liczby kart w kolumnie
    function updateColumnCardCount(columnId) {
        const column = document.querySelector(`.kanban-column[data-column-id="${columnId}"]`);
        const badge = column.querySelector('.badge');
        const cardsCount = column.querySelectorAll('.kanban-card').length;
        const cardLimit = badge.textContent.split('/')[1] || '';
        badge.textContent = cardsCount + (cardLimit ? '/' + cardLimit : '');
    }
});
