<?php

namespace App\Infrastructure\Http\Controllers\Admin;

use App\Application\Admin\AssignableRoles;
use App\Domain\Contracts\IVAOApiServiceContract;
use App\Domain\Contracts\RolesServiceContract;
use App\Domain\Entities\RoleRule;
use App\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RuleController extends Controller
{
    private const ROLE_MX_MEMBER = '1555793312064082030';
    private const ROLE_MX_STAFF = '1555794617994186822';
    private const ROLE_F_MEMBER = '1555793918786928691';

    private RolesServiceContract $rolesService;

    public function __construct(RolesServiceContract $rolesService)
    {
        $this->rolesService = $rolesService;
    }

    public function index(): JsonResponse
    {
        return response()->json(
            $this->rolesService->rules()->map->toArray()
        );
    }

    public function update(
        Request $request,
        AssignableRoles $assignableRoles,
        IVAOApiServiceContract $IVAOAPI
    ): JsonResponse {
        $data = $request->validate([
            'rules' => ['present', 'array', 'max:3'],

            'rules.*.id' => ['required', 'string', 'max:40'],
            'rules.*.name' => ['required', 'string', 'max:60'],

            'rules.*.roles' => ['required', 'array', 'size:1'],
            'rules.*.roles.*' => [
                'required',
                'string',
                'in:' . implode(',', self::allowedRoles()),
            ],

            'rules.*.staff' => ['present', 'array', 'size:0'],
            'rules.*.includeTrial' => ['required', 'boolean'],

            'rules.*.divisionMode' => [
                'required',
                'string',
                'in:in,not_in',
            ],

            'rules.*.divisions' => [
                'required',
                'array',
                'size:1',
            ],

            'rules.*.divisions.*' => ['required', 'string', 'in:MX'],

            'rules.*.minAtcRating' => ['present', 'nullable'],
            'rules.*.minPilotRating' => ['present', 'nullable'],
            'rules.*.minHours' => ['present', 'nullable'],

            'rules.*.requiresGca' => ['required', 'boolean'],
            'rules.*.requiresVaOwnership' => ['required', 'boolean'],
            'rules.*.requiresMexicoStaff' => ['required', 'boolean'],
        ]);

        $seenRoles = [];
        $seenIds = [];

        foreach ($data['rules'] as $index => $rule) {
            $roleId = $rule['roles'][0];
            $ruleId = $rule['id'];

            if (in_array($roleId, $seenRoles, true)) {
                throw ValidationException::withMessages([
                    "rules.$index.roles" =>
                        'Ya existe una regla para este rol.',
                ]);
            }

            if (in_array($ruleId, $seenIds, true)) {
                throw ValidationException::withMessages([
                    "rules.$index.id" =>
                        'El identificador de la regla está duplicado.',
                ]);
            }

            $seenRoles[] = $roleId;
            $seenIds[] = $ruleId;

            $expectedMode = $roleId === self::ROLE_F_MEMBER
                ? RoleRule::DIVISION_NOT_IN
                : RoleRule::DIVISION_IN;

            $expectedMexicoStaff = $roleId === self::ROLE_MX_STAFF;

            $valid =
                $rule['divisionMode'] === $expectedMode
                && $rule['divisions'] === ['MX']
                && $rule['staff'] === []
                && $rule['includeTrial'] === false
                && $rule['requiresMexicoStaff'] === $expectedMexicoStaff
                && $rule['requiresGca'] === false
                && $rule['requiresVaOwnership'] === false
                && ($rule['minAtcRating'] ?? null) === null
                && ($rule['minPilotRating'] ?? null) === null
                && ($rule['minHours'] ?? null) === null;

            if (! $valid) {
                throw ValidationException::withMessages([
                    "rules.$index" =>
                        'La configuración no corresponde al rol seleccionado.',
                ]);
            }
        }

        $rules = RoleRule::collection($data['rules']);

        // Verify every role, including roles already stored.
        $roleIds = $rules
            ->flatMap->getRoles()
            ->unique()
            ->values()
            ->all();

        $refused = $assignableRoles->refused($roleIds);

        if ($refused) {
            throw ValidationException::withMessages([
                'rules' => __(
                    'admin.errors.rolesNotAssignable',
                    [
                        'roles' => implode(
                            ', ',
                            $assignableRoles->namesOf($refused)
                        ),
                    ]
                ),
            ]);
        }

        $this->rolesService->saveAllRoles(
            $rules->map->toArray()->all()
        );

        Log::notice('IVAO Mexico role rules updated', [
            'event' => 'roles.updated',
            'admin' => $IVAOAPI->getUserData()['id'],
            'rules' => $rules->count(),
            'role_ids' => $roleIds,
        ]);

        return response()->json(
            $rules->map->toArray()
        );
    }

    private static function allowedRoles(): array
    {
        return [
            self::ROLE_MX_MEMBER,
            self::ROLE_MX_STAFF,
            self::ROLE_F_MEMBER,
        ];
    }
}