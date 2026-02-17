# Troubleshooting 502 Bad Gateway on Railway

## Quick Checks

### 1. Check Railway Deploy Logs (Most Important!)

Go to Railway → Your Project → Your Service → **Deployments** → Latest Deployment → **View Logs** or **Deploy** tab.

Look for:
- ✅ **"Starting Apache on port 8080"** (or whatever PORT is) = Apache started
- ❌ **"ERROR: Apache configuration test failed!"** = Config issue
- ❌ **"ERROR: PHP is not working!"** = PHP crash
- ❌ **"ERROR: APP_SECRET is required"** = Missing env var
- ❌ Any **PHP Fatal errors** or **Apache errors** after startup

**Copy the last 50-100 lines** of the deploy logs and share them if the issue persists.

---

### 2. Verify Environment Variables

Railway → Your Service → **Variables** (or **Settings → Variables**)

**Required:**
- ✅ **APP_ENV** = `prod`
- ✅ **APP_DEBUG** = `0` (or `false`)
- ✅ **APP_SECRET** = (any random string, e.g. generate with `openssl rand -hex 32`)
- ✅ **DATABASE_URL** = (Railway Postgres URL if using DB)

**Optional but recommended:**
- **PORT** = (Railway sets this automatically, don't override)

If any are missing, **add them** and **redeploy**.

---

### 3. Check if Container is Running

Railway → Your Service → **Metrics** or **Logs**

- Is the container **running** or **crashed**?
- If crashed, check the **exit code** and logs

---

### 4. Test Apache Config Locally (Optional)

If you have Docker locally:

```bash
docker build -t symfony-app .
docker run -p 8080:8080 \
  -e PORT=8080 \
  -e APP_ENV=prod \
  -e APP_DEBUG=0 \
  -e APP_SECRET=test123 \
  symfony-app
```

Then open `http://localhost:8080`. If it works locally but not on Railway, it's likely an **environment variable** issue.

---

### 5. Common Causes

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| 502 immediately | Apache not starting | Check deploy logs for Apache errors |
| 502 after a few seconds | PHP crashing on first request | Check logs for PHP fatal errors, verify APP_ENV/APP_SECRET |
| Container crashes | Missing APP_SECRET or DATABASE_URL wrong | Add env vars in Railway |
| "Apache configuration test failed" | Port config issue | Check entrypoint logs |

---

### 6. Enable More Debugging

If logs aren't showing enough, temporarily add to Railway **Variables**:

- **APP_DEBUG** = `1` (to see PHP errors in response)
- **SYMFONY_LOG_LEVEL** = `debug`

**⚠️ Remove these after debugging** - they expose sensitive info in production.

---

## Next Steps

After checking logs:
1. **If Apache isn't starting**: Check the entrypoint logs for the exact error
2. **If Apache starts but PHP crashes**: Check PHP error logs (usually in `var/log/`)
3. **If everything looks fine in logs**: Railway might be hitting before Apache is ready - try accessing the URL again after 10-20 seconds

**Share the deploy logs** if you need help interpreting them!
