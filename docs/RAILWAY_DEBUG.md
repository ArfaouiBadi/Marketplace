# Railway – Debugging "Application failed to respond"

## 1. Fix applied: listen on Railway’s PORT

Railway injects a **PORT** env var (often **8080**). Apache was listening on **80**, so requests never reached the app.

The **entrypoint** now sets Apache to listen on `$PORT` before starting. Redeploy after pulling the latest code.

---

## 2. Check deploy logs (first place to look)

1. **Railway dashboard** → your project → your **service**.
2. Open **Deployments** → latest deployment → **View logs** (or **Build / Deploy** tabs).
3. Look for:
   - **Build**: errors during `composer install`, Docker build, or copy.
   - **Deploy / Runtime**: PHP/Apache crashes, migration errors, or “Address already in use”.

If the container exits quickly, the logs will show the last command and error (e.g. migration failure or missing env).

---

## 3. Check environment variables

In the same service:

- **Variables** (or **Settings → Variables**).
- Confirm:
  - **PORT** – set by Railway (you don’t need to add it).
  - **APP_ENV** = `prod`
  - **APP_DEBUG** = `0`
  - **APP_SECRET** – set and non-empty.
  - **DATABASE_URL** – set to Railway Postgres URL if you use DB/migrations.

If **DATABASE_URL** is wrong or missing, migrations in the entrypoint can fail and the container may exit before Apache starts.

---

## 4. Check the service is listening on PORT

After a deploy, in **Deploy logs** you should see Apache start without errors. Railway will then route traffic to **PORT** (e.g. 8080). If the entrypoint changes the Apache config to use `$PORT`, the app should respond.

If you still get “Application failed to respond”:

- Ensure the **latest deploy** (with the new entrypoint) is the one running.
- In **Settings**, check **Networking** / **Public networking**: the service should have a **Public URL** and Railway expects the app to listen on **PORT**.

---

## 5. Test the route locally (optional)

With Docker and the same env as Railway:

```bash
docker build -t symfony-app .
docker run -p 8080:8080 -e PORT=8080 -e APP_ENV=prod -e APP_SECRET=dev symfony-app
```

Then open `http://localhost:8080`. If it works locally but not on Railway, the problem is env (e.g. **DATABASE_URL**) or networking on Railway.

---

## 6. Typical causes

| Symptom | What to check |
|--------|----------------|
| “Application failed to respond” | App not listening on **PORT** (fixed by entrypoint), or container exiting (see logs). |
| 502 / 503 | Container crash or health check failing; check **Deploy logs**. |
| Blank / 500 after load | **APP_ENV=prod**, **APP_DEBUG=0**, **DATABASE_URL** and **APP_SECRET** set. |
| Migrations fail in logs | **DATABASE_URL** correct and Postgres service running and linked. |

After changing the entrypoint to use **PORT**, redeploy and re-check the public URL and deploy logs.
