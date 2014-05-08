This software is Open Source/ Freeware.
Written by myself Cuediin and cleanup assistance from JonTheNiceGuy

I have used some code borrowed from droppy from the Jquery forums for a simple menu system.
On top of this I have use some functions from various users from php.net. I will try and go through them so that I can properly acknowledge them.
At this moment in time this set of scripts should be fully working.
It is primary aimed at an Ubuntu environment. I do quite a few file work as it is designed to work with Tiny CA's file structure. I have not colloborated with them and they do not know of this product. This is my first time releasing something into the public domain, so apologies if I have not acknowledged the correct people or done the licensing thing properly.

This CA can allow for it to be a root CA and a SUB CA and a SUB-SUB CA, if you so wish. It allows multiple levels of issuing CA's. In addition, you can use it to self certify multiple domains and thus multiple root CA's due to the way I modularise each of the certificate stores. The Certificate stores should NOT be accessible directly in your www folder. All access is via the various forms, including the use of PHP download headers to facilitate the download of the various certificates, keys, and CSR's.

Functional uses of the CA.
Allows for:-
CA functions
 Creation of a root CA (or multiples there of)
 Creation of an issuing CA (or multiples there of)
 Change the CA private key password
 Switch between CA's on the device.
 Download the CRL file (including a rename option of the extension or the name)


CSR functions
 Create a CSR from scratch
 Copy and paste a CSR in base 64.
 Upload/ import a CSR from a file stored locally.
 Sign a CSR using the CA's keys/certs.
 Download a system held CSR.
 View a CSR held on the system

Certificate functions
 Download a certificate (and optionally rename to .cer or .pfx)
 Revoke a certificate and publish the CRL
 Convert a certificate and private key into a PKCS#12 file

Key functions
 Download the public key component of a key. (Can be converted for use by Putty)
 Download the private key (assuming the private key password is correct). Will also strip the passphrase from the private key, if requested to.
 Check a private key's passphrase. (Including the private key of the CA)

