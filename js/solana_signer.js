/**
 * @file
 * Javascript for Solana Wallet interaction.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.solanaSigner = {
    attach: function (context, settings) {
      // Ensure we only attach the event listener once.
      const signButton = context.querySelector('#btn-sign-solana');
      if (!signButton || signButton.classList.contains('solana-processed')) {
        return;
      }
      signButton.classList.add('solana-processed');

      signButton.addEventListener('click', async () => {
        // Check if the Solana object is injected by the wallet (e.g., Phantom).
        const provider = window.solana;

        if (!provider || !provider.isPhantom) {
          alert("Phantom Wallet not found! Please install it to sign contracts.");
          window.open("https://phantom.app/", "_blank");
          return;
        }

        try {
          // 1. Connect to the wallet.
          // 'onlyIfTrusted' helps avoid popups if already connected previously.
          const response = await provider.connect();
          const publicKey = response.publicKey.toString();
          console.log('Connected to wallet:', publicKey);

          // 2. Prepare the message to sign.
          // This message serves as the proof of intent.
          const contractId = drupalSettings.solana_contracts.contract_id;
          const contractTitle = drupalSettings.solana_contracts.contract_title;
          const message = `I hereby sign the contract "${contractTitle}" (ID: ${contractId}) using my Solana wallet.`;

          // Encode the message.
          const encodedMessage = new TextEncoder().encode(message);

          // 3. Request signature from the wallet.
          const signedMessage = await provider.signMessage(encodedMessage, "utf8");

          // Convert the raw signature (Uint8Array) to a string representation.
          // Note: Typically we use base58 encoding here, but for simplicity in
          // this JS file without external dependencies like bs58, we can use a
          // simple JSON string or hex representation for storage.
          // In a real app, include the 'bs58' library.
          const signatureArray = Array.from(signedMessage.signature);
          const signatureString = JSON.stringify(signatureArray);

          // 4. Send the signature to the Drupal Backend.
          const apiResponse = await fetch('/api/solana/sign', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              // Include CSRF token for security.
              'X-CSRF-Token': drupalSettings.solana_contracts.csrf_token
            },
            body: JSON.stringify({
              contract_id: contractId,
              wallet_address: publicKey,
              signature: signatureString
            })
          });

          const result = await apiResponse.json();

          if (apiResponse.ok) {
            alert("Success! Contract signed. Status: " + result.new_status);
            // Reload the page to show the updated status.
            location.reload();
          } else {
            console.error('API Error:', result);
            alert("Error registering signature: " + (result.error || "Unknown error"));
          }

        } catch (err) {
          console.error("Signing Error:", err);
          alert("Signing failed: " + err.message);
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);