#!/bin/bash

set -e

NAMESPACE="fayen"
SERVICE_NAME="fayen-service"
KUSTOMIZE_PATH="$HOME/projects/LMS-main/k8s/overlays/prod"

echo "[1/4] Applying Kubernetes manifests..."
kubectl apply -k "$KUSTOMIZE_PATH"

echo "[2/4] Checking deployment status..."
kubectl rollout status deployment/fayen-deployment -n "$NAMESPACE" --timeout=300s || true

echo "[3/4] Waiting for LoadBalancer external endpoint..."

for i in $(seq 1 60); do
  LB_HOST=$(kubectl get svc "$SERVICE_NAME" -n "$NAMESPACE" -o jsonpath='{.status.loadBalancer.ingress[0].hostname}' 2>/dev/null || true)
  LB_IP=$(kubectl get svc "$SERVICE_NAME" -n "$NAMESPACE" -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null || true)

  if [ -n "$LB_HOST" ]; then
    echo
    echo "[OK] Load Balancer URL:"
    echo "http://$LB_HOST"
    exit 0
  fi

  if [ -n "$LB_IP" ]; then
    echo
    echo "[OK] Load Balancer URL:"
    echo "http://$LB_IP"
    exit 0
  fi

  echo "Waiting... ($i/60)"
  sleep 10
done

echo
echo "[!] No external Load Balancer endpoint was assigned yet."
echo "[!] Check service status with:"
echo "kubectl get svc -n $NAMESPACE"
exit 1
