document.addEventListener('DOMContentLoaded', () => {
  // Graphique doughnut : Répartition par niveau
  const levelCtx = document.getElementById('levelChart').getContext('2d');
  new Chart(levelCtx, {
    type: 'doughnut',
    data: {
      labels: ['Débutant', 'Intermédiaire'],
      datasets: [{
        data: [68, 74],
        backgroundColor: ['#667eea', '#48bb78'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { padding: 20 }
        }
      }
    }
  });

  // Graphique ligne : Évolution des cours créés par mois
  const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
  new Chart(evolutionCtx, {
    type: 'line',
    data: {
      labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'],
      datasets: [{
        label: 'Cours créés',
        data: [8, 12, 15, 10, 18, 22, 20, 14, 16, 19, 23, 25],
        borderColor: '#667eea',
        backgroundColor: 'rgba(102, 126, 234, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
});