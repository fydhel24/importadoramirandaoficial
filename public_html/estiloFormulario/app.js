function nextStep(currentStep) {
    // Ocultar el paso actual
    const currentStepDiv = document.getElementById(`step-${currentStep}`);
    currentStepDiv.classList.remove('active');

    // Mostrar el siguiente paso
    const nextStepDiv = document.getElementById(`step-${currentStep + 1}`);
    if (nextStepDiv) {
        nextStepDiv.classList.add('active');
    }
}

function selectDepartment(department) {
    document.getElementById('departamentoResumen').textContent = department;
    nextStep(2);
}

function selectPayment(paymentStatus) {
    document.getElementById('pagoResumen').textContent = paymentStatus;
    nextStep(4);
}





