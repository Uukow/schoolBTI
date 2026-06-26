import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../providers/branch_filter_provider.dart';
import '../../core/constants.dart';

/// Reusable branch selector widget
/// Only visible for Super Admin users
class BranchSelector extends StatefulWidget {
  final bool showLabel;
  final EdgeInsets? padding;
  final Color? backgroundColor;

  const BranchSelector({
    super.key,
    this.showLabel = true,
    this.padding,
    this.backgroundColor,
  });

  @override
  State<BranchSelector> createState() => _BranchSelectorState();
}

class _BranchSelectorState extends State<BranchSelector> {
  @override
  Widget build(BuildContext context) {
    return Consumer<BranchFilterProvider>(
      builder: (context, branchProvider, child) {

        // Only show for Super Admin
        if (!branchProvider.showBranchSelector) {
          return const SizedBox.shrink();
        }

        // Show loading state
        if (branchProvider.isLoading && branchProvider.branches.isEmpty) {
          return Container(
            constraints: const BoxConstraints(
              minHeight: 80,
              maxHeight: double.infinity,
            ),
            padding: widget.padding ?? const EdgeInsets.all(16),
            color: widget.backgroundColor ?? Colors.white,
            child: const Center(
              child: CircularProgressIndicator(),
            ),
          );
        }

        // Show error state
        if (branchProvider.error != null && branchProvider.branches.isEmpty) {
          return Container(
            constraints: const BoxConstraints(
              minHeight: 0,
              maxHeight: double.infinity,
            ),
            padding: widget.padding ?? const EdgeInsets.all(16),
            color: widget.backgroundColor ?? Colors.white,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  'Error loading branches: ${branchProvider.error}',
                  style: const TextStyle(color: Colors.red),
                ),
                const SizedBox(height: 8),
                ElevatedButton(
                  onPressed: () => branchProvider.loadBranches(),
                  child: const Text('Retry'),
                ),
              ],
            ),
          );
        }

        return Container(
          constraints: const BoxConstraints(
            minHeight: 0,
            maxHeight: double.infinity,
          ),
          padding: widget.padding ?? const EdgeInsets.all(16),
          color: widget.backgroundColor ?? Colors.white,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (widget.showLabel) ...[
                Text(
                  'Filter by Branch',
                  style: GoogleFonts.montserrat(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Colors.grey[700],
                  ),
                ),
                const SizedBox(height: 8),
              ],
              DropdownButtonFormField<int>(
                isExpanded: true,
                decoration: InputDecoration(
                  labelText: 'Branch',
                  hintText: 'Select branch',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.business),
                  filled: true,
                  fillColor: Colors.grey[50],
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 16,
                  ),
                ),
                initialValue: branchProvider.selectedBranchId,
                items: [
                  const DropdownMenuItem<int>(
                    value: null,
                    child: Row(
                      children: [
                        Icon(Icons.all_inclusive, size: 20),
                        SizedBox(width: 8),
                        Text('All Branches'),
                      ],
                    ),
                  ),
                  ...branchProvider.branches.map((branch) {
                    return DropdownMenuItem<int>(
                      value: branch.id,
                      child: Row(
                        children: [
                          Icon(
                            Icons.business,
                            size: 20,
                            color: AppConstants.primaryColor,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              branch.branchName,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    );
                  }),
                ],
                onChanged: (value) {
                  // Update provider state - it will handle the deferred notification
                  branchProvider.setSelectedBranch(value);
                },
              ),
            ],
          ),
        );
      },
    );
  }
}

/// Compact branch selector for app bar
class BranchSelectorChip extends StatelessWidget {
  const BranchSelectorChip({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<BranchFilterProvider>(
      builder: (context, branchProvider, child) {
        if (!branchProvider.showBranchSelector) {
          return const SizedBox.shrink();
        }

        return PopupMenuButton<int>(
          icon: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.business, size: 18),
              const SizedBox(width: 4),
              Text(
                branchProvider.getSelectedBranchName(),
                style: GoogleFonts.montserrat(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
              const Icon(Icons.arrow_drop_down, size: 18),
            ],
          ),
          itemBuilder: (context) => [
            const PopupMenuItem<int>(
              value: null,
              child: Row(
                children: [
                  Icon(Icons.all_inclusive, size: 20),
                  SizedBox(width: 8),
                  Text('All Branches'),
                ],
              ),
            ),
            const PopupMenuDivider(),
            ...branchProvider.branches.map((branch) {
              return PopupMenuItem<int>(
                value: branch.id,
                child: Row(
                  children: [
                    Icon(
                      Icons.business,
                      size: 20,
                      color: branchProvider.selectedBranchId == branch.id
                          ? AppConstants.primaryColor
                          : Colors.grey,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        branch.branchName,
                        style: TextStyle(
                          fontWeight: branchProvider.selectedBranchId == branch.id
                              ? FontWeight.bold
                              : FontWeight.normal,
                        ),
                      ),
                    ),
                    if (branchProvider.selectedBranchId == branch.id)
                      const Icon(Icons.check, size: 18, color: Colors.green),
                  ],
                ),
              );
            }),
          ],
          onSelected: (value) {
            branchProvider.setSelectedBranch(value);
          },
        );
      },
    );
  }
}

