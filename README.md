# Solana Contract Module

This module provides a framework for creating and signing contracts using the Solana blockchain.

## Features

*   **Contract Entity:** A content entity for storing contract information, including title, description, parties, and status.
*   **Signature Entity:** A content entity for storing digital signatures associated with a contract.
*   **Solana Integration:** Allows users to sign contracts using their Solana wallet (e.g., Phantom).
*   **User Permissions:** Provides granular permissions for creating, viewing, signing, and administering contracts.
*   **Admin Dashboard:** An administrative interface for viewing and managing all contracts.

## Installation

1.  Enable the module at `/admin/modules`.
2.  Grant permissions at `/admin/people/permissions#module-solana_contracts`.

## Usage

*   Users can create contracts at `/contract/add`.
*   Users can view their contracts at `/contract/list`.
*   Users can sign contracts by clicking the "Sign" button on the contract view page.
*   Administrators can manage contracts at `/admin/contracts`.


## Update 

* New entity with the solana account address of the user. The entity is called `solana_account`, is a content entity and is linked to the user entity.