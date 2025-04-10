<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Slot Machine Game</title>
  <style>
    body { 
      font-family: Arial, sans-serif; 
      text-align: center; 
      background-color: #222; 
      color: white; 
      transition: opacity 1.5s ease-in-out; 
      margin: 0; 
      padding: 0;
    }
    .container { 
      width: 400px; 
      margin: 50px auto; 
      background: #333; 
      padding: 20px; 
      border-radius: 10px; 
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.2); 
      transition: opacity 1.5s ease-in-out; 
    }
    button { 
      background-color: gold; 
      color: black; 
      padding: 10px; 
      border: none; 
      margin: 5px; 
      cursor: pointer; 
    }
    .slots { 
      font-size: 50px; 
      margin: 20px; 
      display: flex; 
      justify-content: center; 
      gap: 15px; 
    }
    .slot { 
      width: 50px; 
      height: 50px; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      background: #555; 
      border-radius: 10px; 
    }
    p { 
      font-size: 16px; 
    }
    /* This class fades out the game container */
    .fade-out { 
      opacity: 0; 
      transition: opacity 1.5s ease-in-out; 
    }
    /* Final stats styling */
    #final-stats { 
      display: none; 
      margin: 20px auto; 
      width: 400px;
    }
  </style>
</head>
<body>

  <div class="container" id="game-container">
    <h1>Slot Machine Game</h1>
    <p id="balance">Balance: $100</p>
    <p id="loan-info">To Repay: $0</p>
    <label for="betAmount">Enter Bet (min $30, multiples of 5):</label>
    <input type="number" id="betAmount" min="30" step="5" value="30" />
    <button onclick="placeBet()" id="spinButton">Spin</button>
    <button onclick="takeLoan()">Take Loan</button>
    <div class="slots">
      <div class="slot" id="slot1">❓</div>
      <div class="slot" id="slot2">❓</div>
      <div class="slot" id="slot3">❓</div>
    </div>
    <p id="message"></p>
    <!-- Sound Effects -->
    <audio id="spinSound" src="https://www.fesliyanstudios.com/play-mp3/387"></audio>
    <audio id="winSound" src="https://www.fesliyanstudios.com/play-mp3/438"></audio>
    <audio id="loseSound" src="https://www.fesliyanstudios.com/play-mp3/471"></audio>
    <audio id="gameOverSound" src="https://www.fesliyanstudios.com/play-mp3/642"></audio>
  </div>

  <!-- Final Stats appear here after game over -->
  <div id="final-stats"></div>

  <script>
    let balance = 100,
        loanActive = false,
        loanAmount = 0,
        totalSpent = 0,
        totalWon = 0,
        totalSpins = 0,
        winningSpins = 0,
        totalLoaned = 0;
    const symbols = ["🍒", "7️⃣", "🍋", "🍑", "🍉", "🔔", "🏆", "🍊", "🍇", "🍌"];
    const jackpotMultipliers = { 
      "🍒": 2.5, 
      "7️⃣": 3, 
      "🍋": 2.1, 
      "🍑": 2.2, 
      "🍉": 2.35, 
      "🔔": 2.2, 
      "🏆": 2.3, 
      "🍊": 2.3, 
      "🍇": 2.3, 
      "🍌": 2.3 
    };

    function randomSymbol() {
      return symbols[Math.floor(Math.random() * symbols.length)];
    }

    function roundUpTo5(amount) {
      return Math.ceil(amount / 5) * 5;
    }

    function playSound(soundId) {
      const sound = document.getElementById(soundId);
      sound.currentTime = 0;
      sound.play();
    }

    function updateStats() {
      document.getElementById("balance").innerText = `Balance: $${balance}`;
      document.getElementById("loan-info").innerText = `To Repay: $${loanAmount}`;
    }

    function placeBet() {
      let bet = parseInt(document.getElementById("betAmount").value);
      if (bet < 30 || bet % 5 !== 0 || bet > balance) {
        document.getElementById("message").innerText = "Invalid bet amount!";
        return;
      }
      // Repay loan if active
      if (loanActive) {
        let repayment = Math.ceil(loanAmount * 0.25);
        if (balance < repayment) {
          endGame();
          return;
        }
        balance -= repayment;
        loanAmount -= repayment;
        if (loanAmount <= 0) {
          loanActive = false;
          loanAmount = 0;
        }
      }
      balance -= bet;
      totalSpent += bet;
      totalSpins++;

      document.getElementById("spinButton").disabled = true;
      playSound("spinSound");

      const slot1 = document.getElementById("slot1");
      const slot2 = document.getElementById("slot2");
      const slot3 = document.getElementById("slot3");

      let interval1 = setInterval(() => { slot1.innerText = randomSymbol(); }, 100);
      let interval2 = setInterval(() => { slot2.innerText = randomSymbol(); }, 100);
      let interval3 = setInterval(() => { slot3.innerText = randomSymbol(); }, 100);

      setTimeout(() => {
        clearInterval(interval1);
        slot1.innerText = randomSymbol();
        setTimeout(() => {
          clearInterval(interval2);
          slot2.innerText = randomSymbol();
          setTimeout(() => {
            clearInterval(interval3);
            slot3.innerText = randomSymbol();
            document.getElementById("spinButton").disabled = false;
            updateStats();
            calculateWin(bet, [slot1.innerText, slot2.innerText, slot3.innerText]);
          }, 1000);
        }, 1000);
      }, 1000);
    }

    function calculateWin(bet, slots) {
      let winnings = 0;
      // If all three symbols match, always triple the bet
      if (slots[0] === slots[1] && slots[1] === slots[2]) {
        winnings = roundUpTo5(bet * 3);
        balance += winnings;
        totalWon += winnings;
        winningSpins++;
        playSound("winSound");
        document.getElementById("message").innerText = `Triple Jackpot! You win $${winnings}!`;
      } else if (slots[0] === slots[1] || slots[1] === slots[2] || slots[0] === slots[2]) {
        // Two matching symbols remain unchanged (win 1.5x the bet)
        winnings = roundUpTo5(bet * 1.5);
        balance += winnings;
        totalWon += winnings;
        winningSpins++;
        playSound("winSound");
        document.getElementById("message").innerText = `You win $${winnings}!`;
      } else {
        playSound("loseSound");
        document.getElementById("message").innerText = `You lose $${bet}.`;
      }
      updateStats();
      // End game if balance is 0 OR if balance is under $30 and a loan is active.
      if (balance <= 0 || (balance < 30 && loanActive)) {
        setTimeout(endGame, 1500);
      }
    }

    function takeLoan() {
      if (loanActive) {
        document.getElementById("message").innerText = "You already have an active loan!";
        return;
      }
      const maxLoan = balance * 3;
      let requestedLoan = parseInt(prompt(`Enter loan amount (Multiples of 5, Max: $${maxLoan}):`));
      if (isNaN(requestedLoan) || requestedLoan < 10 || requestedLoan > maxLoan || requestedLoan % 5 !== 0) {
        document.getElementById("message").innerText = "Invalid loan amount!";
        return;
      }
      balance += requestedLoan;
      loanAmount = requestedLoan * 2;
      loanActive = true;
      totalLoaned += requestedLoan;
      updateStats();
    }

    function endGame() {
      playSound("gameOverSound");
      // Fade out the game container.
      document.getElementById("game-container").classList.add("fade-out");
      // After fade-out, hide the game container and display final stats.
      setTimeout(() => {
        document.getElementById("game-container").style.display = "none";
        const finalStatsDiv = document.getElementById("final-stats");
        finalStatsDiv.style.display = "block";
        finalStatsDiv.innerHTML = `
          <div class="container">
            <h1>Game Over!</h1>
            <h3>Final Statistics</h3>
            <p>Total Spins: ${totalSpins}</p>
            <p>Total Money Spent: $${totalSpent}</p>
            <p>Total Money Won: $${totalWon}</p>
            <p>Total Loaned: $${totalLoaned}</p>
            <p>Win Rate: ${totalSpins ? (winningSpins / totalSpins * 100).toFixed(2) : 0}%</p>
          </div>
        `;
      }, 1500);
    }
  </script>

</body>
</html>
