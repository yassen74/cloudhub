resource "aws_db_subnet_group" "fayen_db_subnet_group" {
  name       = "${var.project_name}-db-subnet-group"
  subnet_ids = [aws_subnet.public_1.id, aws_subnet.public_2.id]

  tags = {
    Name = "${var.project_name}-db-subnet-group"
  }
}

resource "aws_security_group" "fayen_rds_sg" {
  name        = "${var.project_name}-rds-sg"
  description = "Allow MySQL access from VPC"
  vpc_id      = aws_vpc.main.id

  ingress {
    description = "MySQL from EKS/VPC"
    from_port   = 3306
    to_port     = 3306
    protocol    = "tcp"
    cidr_blocks = [var.vpc_cidr]
  }

  ingress {
    description = "MySQL from EKS VPC"
    from_port   = 3306
    to_port     = 3306
    protocol    = "tcp"
    cidr_blocks = ["192.168.0.0/16"]
  }

  ingress {
    description = "MySQL from EKS node public IP 1"
    from_port   = 3306
    to_port     = 3306
    protocol    = "tcp"
    cidr_blocks = ["98.92.61.166/32"]
  }

  ingress {
    description = "MySQL from EKS node public IP 2"
    from_port   = 3306
    to_port     = 3306
    protocol    = "tcp"
    cidr_blocks = ["44.201.170.122/32"]
  }

  ingress {
    description = "MySQL from admin laptop"
    from_port   = 3306
    to_port     = 3306
    protocol    = "tcp"
    cidr_blocks = ["156.201.15.1/32"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "${var.project_name}-rds-sg"
  }
}

resource "aws_db_instance" "fayen_db" {
  identifier                      = "${var.project_name}-db"
  allocated_storage               = var.db_allocated_storage
  apply_immediately               = true
  engine                          = "mysql"
  engine_version                  = "8.0"
  instance_class                  = var.db_instance_class
  db_name                         = var.db_name
  username                        = var.db_username
  password                        = var.db_password
  db_subnet_group_name            = aws_db_subnet_group.fayen_db_subnet_group.name
  vpc_security_group_ids          = [aws_security_group.fayen_rds_sg.id]
  backup_retention_period         = 7
  skip_final_snapshot             = true
  deletion_protection             = false
  publicly_accessible             = true
  multi_az                        = false
  storage_encrypted               = true
  auto_minor_version_upgrade      = true
  monitoring_interval             = 60
  monitoring_role_arn             = aws_iam_role.rds_enhanced_monitoring.arn
  enabled_cloudwatch_logs_exports = ["error", "general", "slowquery"]

  tags = {
    Name = "${var.project_name}-db"
  }
}
