#!/usr/bin/env bash
export AWS_PAGER=""

REGION="us-east-1"
CLUSTER="cloudhub-cluster"
NODEGROUP="ng-eb696a2b"
RDS_ID="cloudhub-db"
NAMESPACE="cloudhub"

echo "==> Nodegroup"
aws eks describe-nodegroup \
  --cluster-name "$CLUSTER" \
  --nodegroup-name "$NODEGROUP" \
  --region "$REGION" \
  --query 'nodegroup.scalingConfig' \
  --output table 2>/dev/null || true

echo
echo "==> RDS"
aws rds describe-db-instances \
  --db-instance-identifier "$RDS_ID" \
  --region "$REGION" \
  --query 'DBInstances[0].DBInstanceStatus' \
  --output text 2>/dev/null || true

echo
echo "==> Nodes"
kubectl get nodes 2>/dev/null || true

echo
echo "==> Pods"
kubectl -n "$NAMESPACE" get pods 2>/dev/null || true

echo
echo "==> Service"
kubectl -n "$NAMESPACE" get svc 2>/dev/null || true

echo
echo "==> Rollout"
kubectl rollout status deployment/cloudhub-deployment -n "$NAMESPACE" 2>/dev/null || true

echo
echo "==> Docker"
docker ps 2>/dev/null || true
