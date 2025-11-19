(function (Drupal, solanaWeb3) {
  'use strict';

  Drupal.behaviors.solanaContracts = {
    attach: function (context, settings) {
      const connectButton = document.getElementById('solana-connect');
      const signButton = document.getElementById('solana-sign');
      const rejectButton = document.getElementById('solana-reject');

      if (connectButton) {
        connectButton.addEventListener('click', async () => {
          if (window.solana && window.solana.isPhantom) {
            try {
              await window.solana.connect();
              const publicKey = window.solana.publicKey.toString();
              alert('Connected to wallet: ' + publicKey);
            } catch (err) {
              alert('Could not connect to wallet');
            }
          } else {
            alert('Solana wallet not found. Please install Phantom wallet.');
          }
        });
      }

      if (signButton) {
        signButton.addEventListener('click', async () => {
          if (window.solana && window.solana.isConnected) {
            try {
              const message = new TextEncoder().encode(settings.solana_contracts.contract_hash);
              const signedMessage = await window.solana.signMessage(message, 'utf8');
              const signature = solanaWeb3.bs58.encode(signedMessage.signature);

              // Send the signature to the server
              const contractId = settings.solana_contracts.contract_id;
              const response = await fetch(`/contract/${contractId}/signature`, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify({ signature: signature })
              });

              if (response.ok) {
                alert('Contract signed successfully!');
                window.location.reload();
              } else {
                alert('Could not save signature.');
              }
            } catch (err) {
              alert('Could not sign message');
            }
          } else {
            alert('Please connect your wallet first.');
          }
        });
      }

      if (rejectButton) {
        rejectButton.addEventListener('click', async () => {
          const contractId = settings.solana_contracts.contract_id;
          const response = await fetch(`/contract/${contractId}/reject`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            }
          });

          if (response.ok) {
            alert('Contract rejected successfully!');
            window.location.reload();
          } else {
            alert('Could not reject contract.');
          }
        });
      }
    }
  };
})(Drupal, solanaWeb3);
