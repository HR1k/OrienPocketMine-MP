# OrienMC: WaterdogPE identity forwarding (experimental)

This integration is opt-in. The core must receive downstream connections ONLY from an explicitly trusted proxy for forwarded Xbox identities to be safe.

- WaterdogPE: `online_mode: true`, `use_login_extras: true`.
- Backend: `xbox-auth=off`, restore `player.verify-xuid: true` after confirming existing player data was not created with a different XUID.
- Set the backend environment variable `ORIEN_TRUSTED_WATERDOG_IP` to the actual, fixed private IPv4 address of the WaterdogPE container on the private backend network. Do not use a subnet, wildcard or shared gateway.
- Never publish the backend ports. Isolate the backend network from untrusted containers, prevent source-address spoofing, and reserve the proxy IP so that another container cannot acquire it. An IP allowlist is not cryptographic authentication of the proxy itself.
- Keep the variable unset for normal standalone use. Public IPs and untrusted peer addresses cannot activate forwarded identity.
- Only a strictly boolean true Waterdog_Auth and a valid nonzero decimal uint64 Waterdog_XUID are accepted from that peer. Proxy-specific client claims are removed before vanilla mapping. The original self-signed JWT verification and NetworkSession transport-key checks remain in place.
- The forwarded IP and Minecraft ID are intentionally not used for identity or IP bans.

Before deployment: run CI, build the PHAR using the existing manual Build OrienPocketMine-MP PHAR workflow for this branch, back up the existing backend, and test successful Xbox login, invalid signatures, direct/untrusted login, missing claims, reconnect, and transfer to BedWars. Verify that the same real XUID is stored by NGEssentials on both backends. Do not deploy to production or merge until these end-to-end checks pass.
