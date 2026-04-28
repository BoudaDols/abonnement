# AWS Deployment Guide

Step-by-step commands to create all AWS resources and deploy the Abonnement API on EKS.

---

## Prerequisites

Make sure you have the following installed:
- [AWS CLI](https://docs.aws.amazon.com/cli/latest/userguide/install-cliv2.html)
- [kubectl](https://kubernetes.io/docs/tasks/tools/)
- [Docker](https://docs.docker.com/get-docker/)
- [eksctl](https://eksctl.io/installation/) (optional but recommended for EKS cluster creation)

---

## 1. Configure AWS CLI

```bash
# Configure your admin credentials
aws configure --profile admin
# Enter: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, region (us-east-1), output format (json)

# Use admin profile for all subsequent commands
export AWS_PROFILE=admin

# Verify
aws sts get-caller-identity
```

---

## 2. Create IAM User for GitHub Actions

```bash
# Create the user
aws iam create-user --user-name github-actions-abonnement

# Attach required policies
aws iam attach-user-policy \
  --user-name github-actions-abonnement \
  --policy-arn arn:aws:iam::aws:policy/AmazonEKSClusterPolicy

aws iam attach-user-policy \
  --user-name github-actions-abonnement \
  --policy-arn arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryFullAccess

# Create inline policy for EKS access
aws iam put-user-policy \
  --user-name github-actions-abonnement \
  --policy-name eks-describe-cluster \
  --policy-document '{
    "Version": "2012-10-17",
    "Statement": [
      {
        "Effect": "Allow",
        "Action": ["eks:DescribeCluster", "eks:ListClusters"],
        "Resource": "*"
      }
    ]
  }'

# Create access keys (save these for GitHub Secrets)
aws iam create-access-key --user-name github-actions-abonnement
```

---

## 3. Create ECR Repository

```bash
# Create the repository
aws ecr create-repository \
  --repository-name abonnement \
  --region us-east-1

# Get the registry URL (save this)
aws ecr describe-repositories \
  --repository-names abonnement \
  --region us-east-1 \
  --query 'repositories[0].repositoryUri' \
  --output text
```

---

## 4. Create EKS Cluster

```bash
# Make sure you are using the admin profile
export AWS_PROFILE=admin

# Create the cluster (takes ~15 minutes)
eksctl create cluster \
  --name abonnement-cluster \
  --region us-east-1 \
  --nodegroup-name abonnement-nodes \
  --node-type t3.small \
  --nodes 2 \
  --nodes-min 1 \
  --nodes-max 3 \
  --version 1.32 \
  --managed

# Verify the cluster is running
kubectl get nodes
```

---

## 5. Grant IAM Users Access to EKS Cluster

```bash
# Grant admin user access
aws eks create-access-entry \
  --cluster-name abonnement-cluster \
  --principal-arn arn:aws:iam::<account-id>:user/Aws_CLI \
  --region us-east-1

aws eks associate-access-policy \
  --cluster-name test-cluster \
  --principal-arn arn:aws:iam::891377162237:user/Aws_CLI \
  --policy-arn arn:aws:eks::aws:cluster-access-policy/AmazonEKSClusterAdminPolicy \
  --access-scope type=cluster \
  --region us-east-1

# Grant github-actions-abonnement access
aws eks create-access-entry \
  --cluster-name test-cluster \
  --principal-arn arn:aws:iam::891377162237:user/github-actions-abonnement \
  --region us-east-1

aws eks associate-access-policy \
  --cluster-name test-cluster \
  --principal-arn arn:aws:iam::891377162237:user/github-actions-abonnement \
  --policy-arn arn:aws:eks::aws:cluster-access-policy/AmazonEKSClusterAdminPolicy \
  --access-scope type=cluster \
  --region us-east-1
```

---

## 6. Create MySQL Kubernetes Secret

Choose a username and password for your MySQL database:

```bash
kubectl create secret generic mysql-secrets \
  --from-literal=MYSQL_ROOT_PASSWORD="Donchesina1994@!" \
  --from-literal=MYSQL_USERNAME=abonnement_user \
  --from-literal=MYSQL_PASSWORD="At3stForPrctise"
```

---

## 7. Connect kubectl to EKS

```bash
aws eks update-kubeconfig \
  --region us-east-1 \
  --name abonnement-cluster

# Verify
kubectl cluster-info
```

---

## 8. Create Kubernetes Secret

Where to get each value:

| Secret | Where to get it |
|--------|----------------|
| `DB_USERNAME` | The username you set in step 6 (e.g. `abonnement_user`) |
| `DB_PASSWORD` | The password you set in step 6 |
| `STRIPE_SECRET_KEY` | [Stripe Dashboard](https://dashboard.stripe.com) → Developers → API keys → Secret key |
| `STRIPE_WEBHOOK_SECRET` | [Stripe Dashboard](https://dashboard.stripe.com) → Developers → Webhooks → Signing secret |
| `PAYPAL_CLIENT_ID` | [PayPal Developer](https://developer.paypal.com) → My Apps → your app → Client ID |
| `PAYPAL_CLIENT_SECRET` | [PayPal Developer](https://developer.paypal.com) → My Apps → your app → Secret |
| `PAYPAL_WEBHOOK_ID` | [PayPal Developer](https://developer.paypal.com) → My Apps → your app → Webhooks → Webhook ID |

```bash
kubectl create secret generic abonnement-secrets \
  --from-literal=DB_USERNAME=abonnement_user \
  --from-literal=DB_PASSWORD='At3stForPrctise' \
  --from-literal=STRIPE_SECRET_KEY=sk_live_xxx \
  --from-literal=STRIPE_WEBHOOK_SECRET=whsec_xxx \
  --from-literal=PAYPAL_CLIENT_ID=xxx \
  --from-literal=PAYPAL_CLIENT_SECRET=xxx \
  --from-literal=PAYPAL_WEBHOOK_ID=xxx

# Verify
kubectl get secrets
```

---

## 9. Add GitHub Secrets

Go to your GitHub repository → **Settings → Secrets → Actions** and add:

| Secret | Where to get it |
|--------|----------------|
| `AWS_ACCESS_KEY_ID` | Output of `aws iam create-access-key` in step 2 → `AccessKeyId` |
| `AWS_SECRET_ACCESS_KEY` | Output of `aws iam create-access-key` in step 2 → `SecretAccessKey` |
| `AWS_REGION` | The region you used throughout this guide (e.g. `us-east-1`) |
| `ECR_REPOSITORY` | The repository name created in step 3 (e.g. `abonnement`) |
| `EKS_CLUSTER_NAME` | The cluster name created in step 4 (e.g. `abonnement-cluster`) |

If you lost the `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` from step 2, generate new ones:
```bash
# Delete the old key first if needed
aws iam list-access-keys --user-name github-actions-abonnement
aws iam delete-access-key \
  --user-name github-actions-abonnement \
  --access-key-id <old-key-id>

# Generate new ones
aws iam create-access-key --user-name github-actions-abonnement
# Save AccessKeyId -> AWS_ACCESS_KEY_ID
# Save SecretAccessKey -> AWS_SECRET_ACCESS_KEY
```

---

## 10. Deploy

```bash
# Push to main to trigger the CD pipeline
git push origin main
```

The CD pipeline will:
1. Build the Docker image
2. Push it to ECR
3. Deploy it to EKS

---

## 11. Verify Deployment

```bash
# Check all pods are running (mysql + abonnement)
kubectl get pods

# Check service and get the load balancer URL
kubectl get services

# Wait for MySQL to be ready
kubectl wait --for=condition=ready pod -l app=mysql --timeout=120s

# Run database migrations
kubectl exec -it <abonnement-pod-name> -- php bin/migrate.php

# Run seeders
kubectl exec -it <abonnement-pod-name> -- php bin/seed.php
```

The API will be available at the **LoadBalancer** URL shown in `kubectl get services`.

---

## Teardown (delete all resources)

```bash
# Delete Kubernetes resources (includes MySQL)
kubectl delete -f k8s/

# Delete EKS cluster
eksctl delete cluster --name abonnement-cluster --region us-east-1

# Delete ECR repository
aws ecr delete-repository \
  --repository-name abonnement \
  --force \
  --region us-east-1

# Delete IAM user
aws iam delete-user --user-name github-actions-abonnement
```
