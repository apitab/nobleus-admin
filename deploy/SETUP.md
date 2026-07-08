# Domain & HTTPS Setup

Target mapping (strict separation):

| Domain | App | Traffic |
|---|---|---|
| billing.hargeisawatertech.com | `backend/web` | Billing clerks / admin (browser) |
| api.hargeisawatertech.com | `api/web` | ThingsBoard webhook + machine-to-machine |

## 0. DNS (do this first)

Create two A records pointing to this server:

```
billing.hargeisawatertech.com  A  52.21.182.214
api.hargeisawatertech.com      A  52.21.182.214
```

## 1. Install the Apache vhosts

```bash
sudo cp /home/admin/nobleus-admin/deploy/billing.hargeisawatertech.com.conf /etc/apache2/sites-available/
sudo cp /home/admin/nobleus-admin/deploy/api.hargeisawatertech.com.conf /etc/apache2/sites-available/
sudo a2ensite billing.hargeisawatertech.com api.hargeisawatertech.com
sudo apache2ctl configtest && sudo systemctl reload apache2
```

## 2. HTTPS with Let's Encrypt (after DNS resolves)

```bash
sudo apt-get install -y certbot python3-certbot-apache
sudo certbot --apache -d billing.hargeisawatertech.com -d api.hargeisawatertech.com \
  --redirect --agree-tos -m <your-email>
```

`--redirect` forces all HTTP to HTTPS. Certbot auto-renews via systemd timer.

## 3. Lock down the old IP-based access (strict separation)

Once both domains work over HTTPS, disable the old IP vhosts so the portal
and API are ONLY reachable via their subdomains:

```bash
sudo a2dissite nobleus-admin nobleus-api
sudo systemctl reload apache2
```

NOTE: `nobleus-api.conf` (port 8081) may be used by the mobile app — check
before disabling it, or point the mobile app at api.hargeisawatertech.com.

## 4. ThingsBoard Rule Engine (REST API Call node)

- URL: `https://api.hargeisawatertech.com/v1/telemetry-receive`
- Method: POST
- Header: `X-TB-Token: <TB_WEBHOOK_TOKEN from /home/admin/nobleus-admin/.env>`
- Body:
  `{"deviceName":"$[metadata.deviceName]","reading":"$[absoluteMeterReading_liters]","supplyCode":"$[metadata.ss_supplyCode]","ts":"$[metadata.ts]"}`
- Connect from "Save Timeseries" node on `Success`.

## 5. Reconciliation cron (safety net)

```bash
crontab -e
# add:
0 2 * * * cd /home/admin/nobleus-admin && php yii tb-sync 48 >> /var/log/tb-sync.log 2>&1
```

Requires `TB_URL`, `TB_USERNAME`, `TB_PASSWORD` in `.env` (use a read-only TB user).
