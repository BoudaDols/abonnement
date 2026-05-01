# Local Kubernetes Deployment Guide (Docker Desktop)

Step-by-step guide to deploy the Abonnement API on your local Kubernetes cluster using Docker Desktop.

---

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed
- [kubectl](https://kubernetes.io/docs/tasks/tools/) installed

---

## 1. Enable Kubernetes in Docker Desktop

1. Open **Docker Desktop**
2. Go to **Settings → Kubernetes**
3. Check **Enable Kubernetes**
4. Click **Apply & Restart**
5. Wait until the Kubernetes indicator in the bottom left is **green**

---

## 2. Switch kubectl context to Docker Desktop

```bash
kubectl config use-context docker-desktop

# Verify
kubectl get nodes
```

You should see one node named `docker-desktop` with status `Ready`.

---

## 3. Create Kubernetes Secrets

```bash
# MySQL secrets
kubectl create secret generic mysql-secrets \
  --from-literal=MYSQL_ROOT_PASSWORD=secret \
  --from-literal=MYSQL_USERNAME=abonnement_user \
  --from-literal=MYSQL_PASSWORD=secret

# App secrets
kubectl create secret generic abonnement-secrets \
  --from-literal=DB_USERNAME=abonnement_user \
  --from-literal=DB_PASSWORD=secret \
  --from-literal=STRIPE_SECRET_KEY=sk_test_xxx \
  --from-literal=STRIPE_WEBHOOK_SECRET=whsec_xxx \
  --from-literal=PAYPAL_CLIENT_ID=xxx \
  --from-literal=PAYPAL_CLIENT_SECRET=xxx \
  --from-literal=PAYPAL_WEBHOOK_ID=xxx

# Verify
kubectl get secrets
```

---

## 4. Build the Docker Image Locally

```bash
docker build -t abonnement:latest .

# Verify
docker images | grep abonnement
```

---

## 5. Deploy to Local Kubernetes

```bash
# Build the local image first
docker build -t abonnement:latest .

# Apply all local manifests
kubectl apply -f k8s/local/configmap.yaml
kubectl apply -f k8s/local/mysql.yaml
kubectl apply -f k8s/local/network-policy.yaml
kubectl apply -f k8s/local/deployment.yaml
kubectl apply -f k8s/local/service.yaml
```

---

## 6. Verify Deployment

```bash
# Check all pods are running
kubectl get pods

# Wait for all pods to be ready
kubectl wait --for=condition=ready pod -l app=abonnement --timeout=120s
kubectl wait --for=condition=ready pod -l app=mysql --timeout=120s

# Check services
kubectl get services
```

Expected output:
```
NAME         TYPE           CLUSTER-IP     EXTERNAL-IP   PORT(S)        AGE
abonnement   LoadBalancer   10.96.x.x      localhost     80:xxxxx/TCP   1m
mysql        ClusterIP      None           <none>        3306/TCP       1m
kubernetes   ClusterIP      10.96.0.1      <none>        443/TCP        10m
```

---

## 7. Run Migrations and Seeders

```bash
# Get the abonnement pod name
kubectl get pods

# Run migrations
kubectl exec -it <abonnement-pod-name> -- php bin/migrate.php

# Run seeders
kubectl exec -it <abonnement-pod-name> -- php bin/seed.php
```

---

## 8. Access the API

The API is available at `http://localhost`:

```bash
# Test the API
curl http://localhost/api/plans

# Open Swagger UI in your browser
open http://localhost/api/docs
```

---

## Useful Commands

```bash
# Check pod logs
kubectl logs <pod-name>

# Follow logs in real time
kubectl logs -f <pod-name>

# Restart a deployment
kubectl rollout restart deployment abonnement

# Delete all resources
kubectl delete -f k8s/

# Rebuild and redeploy after code changes
docker build -t abonnement:latest . && \
kubectl rollout restart deployment abonnement
```

---

## Teardown

```bash
# Delete all Kubernetes resources
kubectl delete -f k8s/local/

# Delete secrets
kubectl delete secret abonnement-secrets mysql-secrets

# Delete the Docker image
docker rmi abonnement:latest
```
