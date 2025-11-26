// Challenge Show Page Logic

// State variables initialized from config
let seconds = window.ChallengeConfig.seconds;
let isRunning = window.ChallengeConfig.isRunning;
const { challengeId, isDocente, userId, minPoints, maxPoints } = window.ChallengeConfig;
const timerElement = document.getElementById('timer');
let currentParticipantPosition = 0;
let totalParticipants = 0;
let suggestedPoints = 0;

// WhatsApp Share functionality
// WhatsApp Share functionality
function shareOnWhatsApp() {

    const codeElement = document.getElementById('challengeCode');
    if (!codeElement) {

        alert('Error: No se encontró el código del desafío.');
        return;
    }

    const codeText = codeElement.textContent.trim();
    if (!codeText) {

        alert('Error: El código está vacío.');
        return;
    }

    const message = `¡Únete a mi desafío en Classroom Clash! 🚀\n\nCódigo de acceso: *${codeText}*\n\nIngresa aquí: ${window.location.origin}`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;



    // Try opening in new tab
    const newWindow = window.open(whatsappUrl, '_blank');

    // Check if blocked
    if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
        alert('Por favor habilita las ventanas emergentes para compartir en WhatsApp.');
        // Fallback to current window
        window.location.href = whatsappUrl;
    }
}

function formatTime(totalSeconds) {
    const h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
    const m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
    const s = (totalSeconds % 60).toString().padStart(2, '0');
    return `${h}:${m}:${s}`;
}

function calculateSuggestedPoints(position, total) {
    if (total <= 1) return maxPoints;
    const pointsRange = maxPoints - minPoints;
    const positionFactor = (total - position) / (total - 1);
    return Math.round(minPoints + (pointsRange * positionFactor));
}

setInterval(() => {
    if (isRunning) {
        seconds++;
        if (timerElement) timerElement.textContent = formatTime(seconds);
    }
}, 1000);

function fetchData() {
    fetch(`/challenge/${challengeId}/data`)
        .then(response => response.json())
        .then(data => {
            seconds = data.challenge.current_time_seconds;
            isRunning = data.challenge.is_running;
            if (timerElement) timerElement.textContent = formatTime(seconds);
            updateParticipantsGrid(data.participants);
        })
        .catch(() => { });
}

function updateParticipantsGrid(participants) {
    const scoreModal = document.getElementById('scoreModal');
    const validateModal = document.getElementById('validateModal');

    if (scoreModal && scoreModal.style.display === 'flex') return;
    if (validateModal && validateModal.style.display === 'flex') return;

    participants.forEach((p, index) => {
        let card = document.getElementById(`participant-card-${p.id}`);

        if (!card) {
            if (participants.length !== document.querySelectorAll('.participant-card:not(.placeholder)').length) {
                location.reload();
                return;
            }
        } else {
            // Update Rank Styling (preserve current-user class)
            let classes = `participant-card ${index < 3 ? 'top-rank rank-' + (index + 1) : ''}`;
            if (p.user_id === userId) {
                classes += ' current-user';
            }
            card.className = classes;

            const badge = card.querySelector('.position-badge');
            if (index === 0) badge.innerHTML = '<span class="medal">🥇</span>';
            else if (index === 1) badge.innerHTML = '<span class="medal">🥈</span>';
            else if (index === 2) badge.innerHTML = '<span class="medal">🥉</span>';
            else badge.innerHTML = `<span class="rank-number">#${index + 1}</span>`;

            card.querySelector('.points-value').textContent = p.points;

            if (isDocente) {
                const settingsBtn = card.querySelector('.btn-icon.settings');
                if (settingsBtn) {
                    settingsBtn.setAttribute('onclick', `openScoreModal(${p.id}, '${p.name}', ${p.points})`);
                }

                const actionsDiv = card.querySelector('.card-actions');
                let validateBtn = card.querySelector('.btn-icon.validate');

                if (p.finished_at && !validateBtn) {
                    validateBtn = document.createElement('button');
                    validateBtn.type = 'button';
                    validateBtn.className = 'btn-icon validate';
                    validateBtn.innerHTML = '✅';
                    validateBtn.title = 'Validar Desafío';
                    validateBtn.setAttribute('onclick', `openValidateModal(${p.id}, '${p.name}')`);
                    actionsDiv.appendChild(validateBtn);
                }
            }

            const infoDiv = card.querySelector('.student-info');
            let timeDiv = infoDiv.querySelector('.submission-time');
            if (p.finished_at) {
                if (!timeDiv) {
                    timeDiv = document.createElement('div');
                    timeDiv.className = 'submission-time';
                    infoDiv.appendChild(timeDiv);
                }
                timeDiv.textContent = `⏱ ${p.formatted_time}`;
            }
        }
    });
}

setInterval(fetchData, 3000);

const modal = document.getElementById('scoreModal');
const modalName = document.getElementById('modalStudentName');
const modalInput = document.getElementById('modalPointsInput');
if (modalInput) {
    modalInput.addEventListener('input', function () {
        let val = parseInt(this.value);
        if (val > maxPoints) this.value = maxPoints;
        if (val < 0) this.value = 0;
    });
}
const modalForm = document.getElementById('scoreForm');

function openScoreModal(participantId, name, points) {
    modalName.textContent = 'Ajustar: ' + name;
    modalInput.value = points;
    modalForm.action = `/challenge/${challengeId}/participant/${participantId}/score`;

    // Find participant position
    const participantCards = document.querySelectorAll('.participant-card:not(.placeholder)');
    totalParticipants = participantCards.length;

    participantCards.forEach((card, index) => {
        if (card.id === `participant-card-${participantId}`) {
            currentParticipantPosition = index + 1; // 1-based position
        }
    });

    // Calculate and show suggested points
    suggestedPoints = calculateSuggestedPoints(currentParticipantPosition, totalParticipants);
    document.getElementById('suggestedPointsValue').textContent = suggestedPoints;
    document.getElementById('suggestedPointsInfo').style.display = 'block';

    modal.style.display = 'flex';
}

function closeScoreModal() {
    if (modal) modal.style.display = 'none';
    const info = document.getElementById('suggestedPointsInfo');
    if (info) info.style.display = 'none';
}

function adjustModalScore(amount) {
    let current = parseInt(modalInput.value) || 0;
    let newVal = current + amount;
    if (newVal < 0) newVal = 0;
    if (newVal > maxPoints) newVal = maxPoints;
    modalInput.value = newVal;
}

function applySuggestedPoints() {
    modalInput.value = suggestedPoints;
}

const validateModal = document.getElementById('validateModal');
const validateModalName = document.getElementById('validateModalStudentName');
const validateForm = document.getElementById('validateForm');
const penaltyInput = document.getElementById('penaltySeconds');

function openValidateModal(participantId, name) {
    validateModalName.textContent = 'Validar: ' + name;
    penaltyInput.value = 0;
    validateForm.action = `/challenge/${challengeId}/participant/${participantId}/validate`;
    validateModal.style.display = 'flex';
}

function closeValidateModal() {
    if (validateModal) validateModal.style.display = 'none';
}

function setPenalty(seconds) {
    penaltyInput.value = seconds;
}



function handleAjaxForm(form, callback) {
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    callback();
                    fetchData(); // Refresh grid immediately
                } else {

                    alert('Hubo un error al guardar. Por favor intenta de nuevo.');
                }
            })
            .catch(error => {

                alert('Hubo un error al guardar. Por favor intenta de nuevo.');
            });
    });
}

document.addEventListener('DOMContentLoaded', function () {


    const modalForm = document.getElementById('scoreForm');
    const validateForm = document.getElementById('validateForm');

    if (modalForm) {

        handleAjaxForm(modalForm, closeScoreModal);
    } else {

    }

    if (validateForm) {

        handleAjaxForm(validateForm, closeValidateModal);
    } else {

    }
});

// Roulette functionality
let rouletteParticipants = [];
let selectedWinner = null;

function openRouletteModal() {
    // Fetch current participants
    fetch(`/challenge/${challengeId}/data`)
        .then(response => response.json())
        .then(data => {
            rouletteParticipants = data.participants;
            renderParticipantsList();
            document.getElementById('rouletteModal').style.display = 'flex';
            showRouletteStep(1);
        });
}

function closeRouletteModal() {
    document.getElementById('rouletteModal').style.display = 'none';
    resetRoulette();
}

function renderParticipantsList() {
    const container = document.getElementById('participantsList');
    container.innerHTML = '';

    rouletteParticipants.forEach(p => {
        const div = document.createElement('div');
        div.className = 'participant-checkbox';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = `roulette-participant-${p.id}`;
        checkbox.value = p.id;
        checkbox.checked = !p.participated; // Auto-check if not participated

        const label = document.createElement('label');
        label.htmlFor = `roulette-participant-${p.id}`;
        label.textContent = `${p.name} ${p.participated ? '(Ya participó)' : ''}`;

        div.appendChild(checkbox);
        div.appendChild(label);
        container.appendChild(div);
    });
}

function selectAllParticipants() {
    document.querySelectorAll('#participantsList input[type="checkbox"]').forEach(cb => cb.checked = true);
}

function deselectAllParticipants() {
    document.querySelectorAll('#participantsList input[type="checkbox"]').forEach(cb => cb.checked = false);
}

function getSelectedParticipants() {
    const selected = [];
    document.querySelectorAll('#participantsList input[type="checkbox"]:checked').forEach(cb => {
        const participant = rouletteParticipants.find(p => p.id == cb.value);
        if (participant) selected.push(participant);
    });
    return selected;
}

function spinRoulette() {
    const selected = getSelectedParticipants();

    if (selected.length === 0) {
        alert('Debes seleccionar al menos un participante');
        return;
    }

    showRouletteStep(2);

    // Spinning animation
    const spinnerDisplay = document.getElementById('spinnerDisplay');
    let counter = 0;
    const spinDuration = 3000; // 3 seconds
    const interval = 100; // Change name every 100ms

    const spinInterval = setInterval(() => {
        const randomIndex = Math.floor(Math.random() * selected.length);
        spinnerDisplay.textContent = selected[randomIndex].name;
        counter += interval;

        if (counter >= spinDuration) {
            clearInterval(spinInterval);
            // Select final winner
            const winnerIndex = Math.floor(Math.random() * selected.length);
            selectedWinner = selected[winnerIndex];
            showWinner();
        }
    }, interval);
}

function showWinner() {
    document.getElementById('winnerName').textContent = selectedWinner.name;
    document.getElementById('customRoulettePoints').value = '';
    // Show current points
    const pointsDisplay = document.getElementById('winnerCurrentPoints');
    pointsDisplay.textContent = `Puntos actuales: ${selectedWinner.points} pts`;
    pointsDisplay.style.fontWeight = 'normal';
    pointsDisplay.style.color = '#6b7280';
    showRouletteStep(3);
}

function assignRoulettePoints(points) {
    if (!selectedWinner) return;

    const currentPoints = parseInt(selectedWinner.points) || 0;
    if (currentPoints + points > maxPoints) {
        alert(`No puedes asignar más puntos. El límite es ${maxPoints}.`);
        return;
    }

    sendRoulettePoints(selectedWinner.id, points);
}

function assignCustomRoulettePoints() {
    const points = parseInt(document.getElementById('customRoulettePoints').value);

    if (!points || points < 1) {
        alert('Ingresa una cantidad válida de puntos');
        return;
    }

    assignRoulettePoints(points);
}

function sendRoulettePoints(participantId, points) {
    const formData = new FormData();
    formData.append('participant_id', participantId);
    formData.append('points', points);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch(`/challenge/${challengeId}/roulette`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the current points display
                const pointsDisplay = document.getElementById('winnerCurrentPoints');
                pointsDisplay.textContent = `Puntos actuales: ${data.participant.points} pts`;
                pointsDisplay.style.fontWeight = 'bold';
                pointsDisplay.style.color = '#10b981';

                fetchData(); // Refresh main grid
                // Update roulette participants list
                const pIndex = rouletteParticipants.findIndex(p => p.id === participantId);
                if (pIndex !== -1) {
                    rouletteParticipants[pIndex].participated = true;
                    rouletteParticipants[pIndex].points = data.participant.points;
                }
            }
        })
        .catch(error => {

            alert('Hubo un error al asignar puntos');
        });
}

function resetRoulette() {
    selectedWinner = null;
    showRouletteStep(1);
    renderParticipantsList();
}

function showRouletteStep(step) {
    document.getElementById('rouletteStep1').style.display = step === 1 ? 'block' : 'none';
    document.getElementById('rouletteStep2').style.display = step === 2 ? 'block' : 'none';
    document.getElementById('rouletteStep3').style.display = step === 3 ? 'block' : 'none';
}

// Expose functions to window for HTML onclick attributes
window.shareOnWhatsApp = shareOnWhatsApp;
window.openScoreModal = openScoreModal;
window.closeScoreModal = closeScoreModal;
window.adjustModalScore = adjustModalScore;
window.applySuggestedPoints = applySuggestedPoints;
window.openValidateModal = openValidateModal;
window.closeValidateModal = closeValidateModal;
window.setPenalty = setPenalty;
window.openRouletteModal = openRouletteModal;
window.closeRouletteModal = closeRouletteModal;
window.selectAllParticipants = selectAllParticipants;
window.deselectAllParticipants = deselectAllParticipants;
window.spinRoulette = spinRoulette;
window.assignRoulettePoints = assignRoulettePoints;
window.assignCustomRoulettePoints = assignCustomRoulettePoints;
window.resetRoulette = resetRoulette;


/* Teams Modal Functions */

function openTeamsModal() {
    document.getElementById('teamsModal').style.display = 'flex';
}

function closeTeamsModal() {
    document.getElementById('teamsModal').style.display = 'none';
}

function adjustTeamSize(change) {
    const input = document.getElementById('teamSizeInput');
    let newValue = parseInt(input.value) + change;
    if (newValue >= 2 && newValue <= 10) {
        input.value = newValue;
    }
}

/* Add Student Modal Functions */
function openAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'flex';
}

function closeAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'none';
}

// Expose functions to window
window.openTeamsModal = openTeamsModal;
window.closeTeamsModal = closeTeamsModal;
window.adjustTeamSize = adjustTeamSize;
window.openAddStudentModal = openAddStudentModal;
window.closeAddStudentModal = closeAddStudentModal;

// Consolidated window.onclick handler for all modals
window.onclick = function (event) {
    const scoreModal = document.getElementById('scoreModal');
    const validateModal = document.getElementById('validateModal');
    const rouletteModal = document.getElementById('rouletteModal');
    const teamsModal = document.getElementById('teamsModal');
    const addStudentModal = document.getElementById('addStudentModal');

    if (event.target == scoreModal) closeScoreModal();
    if (event.target == validateModal) closeValidateModal();
    if (event.target == rouletteModal) closeRouletteModal();
    if (event.target == teamsModal) closeTeamsModal();
    if (event.target == addStudentModal) closeAddStudentModal();
}
