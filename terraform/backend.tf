terraform {
  backend "s3" {
    bucket = "itverse-terraform-state-1777049454"
    key    = "cloudhub/terraform.tfstate"
    region = "us-east-1"
  }
}
