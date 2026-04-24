resource "aws_db_subnet_group" "fayen_db_subnet_group_private" {
  name       = "${var.project_name}-db-subnet-group-private"
  subnet_ids = ["subnet-08306da91c8c2c111", "subnet-068b637bdc9913dcd"]

  tags = {
    Name = "${var.project_name}-db-subnet-group-private"
  }
}

resource "aws_security_group" "fayen_rds_sg_private" {
  name        = "${var.project_name}-rds-sg-private"
  description = "Allow MySQL from EKS cluster"
  vpc_id      = "vpc-068ebbe6703ef1c5b"

  ingress {
    description = "MySQL from EKS cluster SG"
    from_port   = 3306
    to_port     = 3306
    protocol    = "tcp"
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "${var.project_name}-rds-sg-private"
  }
}

resource "aws_db_instance" "fayen_db_private" {
  identifier                      = "${var.project_name}-db-private"
  allocated_storage               = var.db_allocated_storage
  apply_immediately               = true
  engine                          = "mysql"
  engine_version                  = "8.0"
  instance_class                  = var.db_instance_class
  db_name                         = var.db_name
  username                        = var.db_username
  password                        = var.db_password
  db_subnet_group_name            = aws_db_subnet_group.fayen_db_subnet_group_private.name
  vpc_security_group_ids          = [aws_security_group.fayen_rds_sg_private.id]
  backup_retention_period         = 7
  skip_final_snapshot             = true
  deletion_protection             = false
  publicly_accessible             = false
  multi_az                        = false
  storage_encrypted               = true
  auto_minor_version_upgrade      = true
  monitoring_interval             = 60
  monitoring_role_arn             = aws_iam_role.rds_enhanced_monitoring.arn
  enabled_cloudwatch_logs_exports = ["error", "general", "slowquery"]

  tags = {
    Name = "${var.project_name}-db-private"
  }
}

output "rds_private_endpoint" {
  value = aws_db_instance.fayen_db_private.address
}

output "rds_private_port" {
  value = aws_db_instance.fayen_db_private.port
}
