(function (Drupal, solanaWeb3) {
  'use strict';



  Drupal.behaviors.solanaContracts = {
    attach: function (context, settings) {
      const connectButton = document.getElementById('solana-connect');
      const signButton = document.getElementById('solana-sign');
      const rejectButton = document.getElementById('solana-reject');

      if (connectButton) {
        connectButton.addEventListener('click', async (e) => {
          e.preventDefault();
          console.log('Connect button clicked');
          console.log('window.solana:', window.solana);
          if (window.solana && window.solana.isPhantom) {
            console.log('Phantom wallet detected');
            try {
              await window.solana.connect();
              const publicKey = window.solana.publicKey.toString();
              console.log('Connected to wallet:', publicKey);
              alert('Connected to wallet: ' + publicKey);
            } catch (err) {
              console.error('Connection error:', err);
              alert('Could not connect to wallet');
            }
          } else {
            console.log('Phantom wallet not found');
            alert('Solana wallet not found. Please install Phantom wallet.');
          }
        });
      }

      if (signButton) {
        signButton.addEventListener('click', async () => {
          let signature;
          let publicKey;

          const bs58 = window.bs58 || solanaWeb3.bs58;
          if (!bs58) {
            alert('Error: bs58 library not loaded. Please refresh the page.');
            return;
          }

          try {
            // Case 1: Use stored keys from Drupal
            if (settings.solana_contracts.solana_account && settings.solana_contracts.solana_account.private_key) {
              const secretKey = new Uint8Array(settings.solana_contracts.solana_account.private_key.split(',').map(Number));
              const keypair = solanaWeb3.Keypair.fromSecretKey(secretKey);
              const message = new TextEncoder().encode(settings.solana_contracts.contract_hash);
              const signatureBytes = nacl.sign.detached(message, keypair.secretKey);
              signature = bs58.encode(signatureBytes);
              publicKey = keypair.publicKey.toString();
              console.log('Signed with stored key');
            }
            // Case 2: Connect to Wallet (if available and connected)
            else if (window.solana && window.solana.isConnected) {
              const message = new TextEncoder().encode(settings.solana_contracts.contract_hash);
              const signedMessage = await window.solana.signMessage(message, 'utf8');
              signature = bs58.encode(signedMessage.signature);
              console.log('Signed with wallet');
            }
            // Case 3: Auto-generate new keys
            else {
              // Generate new keypair
              const keypair = solanaWeb3.Keypair.generate();
              publicKey = keypair.publicKey.toString();
              const secretKey = keypair.secretKey.toString();

              // Save keys to server
              const saveResponse = await fetch('/solana/save-keys', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                  publicKey: publicKey,
                  secretKey: secretKey
                })
              });

              if (!saveResponse.ok) {
                throw new Error('Failed to save generated keys');
              }

              // Sign with new key
              const message = new TextEncoder().encode(settings.solana_contracts.contract_hash);
              const signatureBytes = nacl.sign.detached(message, keypair.secretKey);
              signature = bs58.encode(signatureBytes);
              console.log('Generated and signed with new key');
            }

            if (!signature) {
              throw new Error('No signature generated');
            }

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
              console.error(await response.text());
            }

          } catch (err) {
            console.error(err);
            alert('Could not sign message: ' + err.message);
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
