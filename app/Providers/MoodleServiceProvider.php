<?php

namespace App\Providers;

use App\Services\Moodle\LearningPathService;
use App\Services\Moodle\OrganizationService;
use App\Services\Moodle\CourseService;
use App\Services\Moodle\QuizService;
use App\Services\Moodle\UserService;
use Illuminate\Support\ServiceProvider;

class MoodleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('moodle', function () {
            return new class {
                public static function getCourses(string $visibility = 'online'): array
                {
                    return CourseService::getCourses($visibility);
                }

                public static function getTmsCourses(string $type = null): array
                {
                    return CourseService::getTmsCourses($type);
                }
                
                public static function getCourseParticipants(int $courseId): int
                {
                    return CourseService::getCourseParticipants($courseId);
                }
                
                public static function getCourseCompleted(int $courseId): int
                {
                    return CourseService::getCourseCompleted($courseId);
                }

                public static function getTmsExams(): array
                {
                    return QuizService::getTmsExams();
                }

                public static function getQuizParticipants(int $quizId): int
                {
                    return QuizService::getQuizParticipants($quizId);
                }

                public static function getQuizCompleted(int $quizId): int
                {
                    return QuizService::getQuizCompleted($quizId);
                }

                public static function getQuizStatus($quiz): string
                {
                    return QuizService::getQuizStatus($quiz);
                }

                public static function getCoursesByType(string $type): array
                {
                    return CourseService::getCoursesByType($type);
                }

                public static function createCourse(array $data): int
                {
                    return CourseService::createCourse($data);
                }

                public static function deleteCourse(int $id): bool
                {
                    return CourseService::deleteCourse($id);
                }

                public static function updateCourse(int $id, array $data): bool
                {
                    return CourseService::updateCourse($id, $data);
                }

                public static function getCourseImage(int $courseId): ?string
                {
                    return CourseService::getCourseImage($courseId);
                }

                public static function updateCourseCustomField(int $courseId, string $value, int $fieldId = 8): bool
                {
                    return CourseService::updateCourseCustomField($courseId, $value, $fieldId);
                }

                public static function getCourse(int $id): ?object
                {
                    return CourseService::getCourse($id);
                }

                public static function getUsers(): array
                {
                    return UserService::getUsers();
                }

                public static function getAllUsers(): array
                {
                    return UserService::getAllUsers();
                }

                public static function getUser(int $id): ?object
                {
                    return UserService::getUser($id);
                }

                public static function getUserByEmail(string $email): ?object
                {
                    return UserService::getUserByEmail($email);
                }

                public static function getUserByUsername(string $username): ?object
                {
                    return UserService::getUserByUsername($username);
                }

                public static function enrolUserWithTime(int $userid, int $courseid, int $timestart = 0, int $timeend = 0, int $roleid = 5): bool
                {
                    return UserService::enrolUserWithTime($userid, $courseid, $timestart, $timeend, $roleid);
                }

                public static function createUser(array $data): int
                {
                    return UserService::createUser($data);
                }
                
                public static function hashPassword(string $password): string
                {
                    return UserService::hashPassword($password);
                }

                public static function updateUser(int $id, array $data): bool
                {
                    return UserService::updateUser($id, $data);
                }

                public static function deleteUser(int $id): bool
                {
                    return UserService::deleteUser($id);
                }

                public static function getQuizAttempts(): array
                {
                    return QuizService::getQuizAttempts();
                }

                public static function getQuizzes(): array
                {
                    return QuizService::getQuizzes();
                }

                public static function getQuiz(int $id): ?object
                {
                    return QuizService::getQuiz($id);
                }

                public static function createQuiz(array $data): int
                {
                    return QuizService::createQuiz($data);
                }

                public static function updateQuiz(int $id, array $data): bool
                {
                    return QuizService::updateQuiz($id, $data);
                }

                public static function deleteQuiz(int $id): bool
                {
                    return QuizService::deleteQuiz($id);
                }

                public static function getQuestionsInQuiz(int $quizid): array
                {
                    return QuizService::getQuestionsInQuiz($quizid);
                }

                public static function addQuestionToQuiz(int $quizid, int $questionid, int $slot = 0, float $mark = 1.0): bool
                {
                    return QuizService::addQuestionToQuiz($quizid, $questionid, $slot, $mark);
                }

                public static function removeQuestionFromQuiz(int $slotid): bool
                {
                    return QuizService::removeQuestionFromQuiz($slotid);
                }

                 public static function getQuestions(): array
                 {
                     return QuizService::getQuestions();
                 }

                  public static function getQuestion(int $id): ?object
                  {
                      return QuizService::getQuestion($id);
                  }

                  public static function getQuestionTypes(): array
                  {
                      return QuizService::getQuestionTypes();
                  }

                  public static function createQuestion(array $data): int
                  {
                      return QuizService::createQuestion($data);
                  }

                  public static function updateQuestion(int $id, array $data): bool
                  {
                      return QuizService::updateQuestion($id, $data);
                  }

                  public static function deleteQuestion(int $id): bool
                  {
                      return QuizService::deleteQuestion($id);
                  }

                  // Question Sets (Bộ đề)
                  public static function getQuestionSets(): array
                  {
                      return QuizService::getQuestionSets();
                  }

                  public static function getQuestionSet(int $id): ?object
                  {
                      return QuizService::getQuestionSet($id);
                  }

                  public static function getQuestionsInSet(int $setId): array
                  {
                      return QuizService::getQuestionsInSet($setId);
                  }

                  public static function createQuestionSet(array $data): int
                  {
                      return QuizService::createQuestionSet($data);
                  }

                  public static function updateQuestionSet(int $id, array $data): bool
                  {
                      return QuizService::updateQuestionSet($id, $data);
                  }

                  public static function deleteQuestionSet(int $id): bool
                  {
                      return QuizService::deleteQuestionSet($id);
                  }

                public static function getEnrolledUsers(int $courseid): array
                {
                    return CourseService::getEnrolledUsers($courseid);
                }


                // Enrollment helpers moved to later in file (Phase 1 handled elsewhere)

                public static function enrolUser(int $userid, int $courseid, int $roleid = 5): bool
                {
                    return CourseService::enrolUser($userid, $courseid, $roleid);
                }

                public static function enrolUsersToCourse(array $userIds, int $courseid, int $roleid = 5, string $enrolMethod = 'manual'): bool
                {
                    return CourseService::enrolUsersToCourse($userIds, $courseid, $roleid, $enrolMethod);
                }

                public static function getGroups(int $courseid): array
                {
                    return CourseService::getGroups($courseid);
                }

                public static function getGroupMembers(int $groupid): array
                {
                    return CourseService::getGroupMembers($groupid);
                }

                public static function enrolUsersToGroup(array $groupIds, int $courseid, int $roleid = 5): bool
                {
                    return CourseService::enrolUsersToGroup($groupIds, $courseid, $roleid);
                }

                public static function getCohortsForCourse(int $courseid): array
                {
                    return CourseService::getCohortsForCourse($courseid);
                }

                public static function getCohorts(): array
                {
                    return CourseService::getCohorts();
                }

                public static function getEnrolledCohorts(int $courseid): array
                {
                    return CourseService::getEnrolledCohorts($courseid);
                }

                public static function getCohortMembers(int $cohortid): array
                {
                    return CourseService::getCohortMembers($cohortid);
                }

                public static function enrolUsersToCohorts(array $cohortIds, int $courseid, int $roleid = 5): bool
                {
                    return CourseService::enrolUsersToCohorts($cohortIds, $courseid, $roleid);
                }

                public static function unenrolCohort(int $cohortid, int $courseid): bool
                {
                    return CourseService::unenrolCohort($cohortid, $courseid);
                }

                public static function uploadCourseImage(int $courseId, $file): bool
                {
                    return CourseService::uploadCourseImage($courseId, $file);
                }

                public static function unenrolUser(int $userid, int $courseid): bool
                {
                    return CourseService::unenrolUser($userid, $courseid);
                }

                public static function getCategories(): array
                {
                    return CourseService::getCategories();
                }
                public static function updateCategory(int $id, array $data): bool
                {
                    return CourseService::updateCategory($id, $data);
                }
                public static function deleteCategory(int $id): bool
                {
                    return CourseService::deleteCategory($id);
                }

                // Learning Path functions
                public static function getLearningPaths(): array
                {
                    return LearningPathService::getLearningPaths();
                }

                public static function getLearningPath(int $id): ?object
                {
                    return LearningPathService::getLearningPath($id);
                }

                public static function createLearningPath(array $data): int
                {
                    return LearningPathService::createLearningPath($data);
                }

                public static function updateLearningPath(int $id, array $data): bool
                {
                    return LearningPathService::updateLearningPath($id, $data);
                }

                public static function deleteLearningPath(int $id): bool
                {
                    return LearningPathService::deleteLearningPath($id);
                }

                public static function getLearningPathNotifications(int $lptId): ?object
                {
                    return LearningPathService::getLearningPathNotifications($lptId);
                }

                public static function saveLearningPathNotifications(int $lptId, array $data): bool
                {
                    return LearningPathService::saveLearningPathNotifications($lptId, $data);
                }

                public static function getLearningPathCourses(int $lptId): array
                {
                    return LearningPathService::getLearningPathCourses($lptId);
                }

                // Progress API planned for Phase 2 (not implemented here)

                public static function getLearningPathAvailableCourses(): array
                {
                    return LearningPathService::getLearningPathAvailableCourses();
                }

                public static function getLearningPathAvailableCatalogueCourses(): array
                {
                    return LearningPathService::getLearningPathAvailableCatalogueCourses();
                }

                public static function addMoodleCourseToLearningPath(int $lptId, int $courseId, int $sortorder = 0): bool
                {
                    return LearningPathService::addMoodleCourseToLearningPath($lptId, $courseId, $sortorder);
                }

                public static function addCatalogueCourseToLearningPath(int $lptId, string $catalogueCode, int $sortorder = 0): bool
                {
                    return LearningPathService::addCatalogueCourseToLearningPath($lptId, $catalogueCode, $sortorder);
                }

                public static function addCourseToLearningPath(int $lptId, int $courseId, int $sortorder = 0): bool
                {
                    return LearningPathService::addCourseToLearningPath($lptId, $courseId, $sortorder);
                }

                public static function removeCourseFromLearningPath(int $lineId): bool
                {
                    return LearningPathService::removeCourseFromLearningPath($lineId);
                }

                public static function updateLearningPathLineRequired(int $lineId, bool $required): bool
                {
                    return LearningPathService::updateLearningPathLineRequired($lineId, $required);
                }

                public static function updateLearningPathLineCredit(int $lineId, int $credit): bool
                {
                    return LearningPathService::updateLearningPathLineCredit($lineId, $credit);
                }

                public static function getLearningPathUsers(int $lptId): array
                {
                    return LearningPathService::getLearningPathUsers($lptId);
                }

                public static function getLearningPathProgress(int $lptId): array
                {
                    return LearningPathService::getLearningPathProgress($lptId);
                }

                public static function getAllUsersForLearningPath(): array
                {
                    return LearningPathService::getAllUsersForLearningPath();
                }

                public static function getCohortsForLearningPath(): array
                {
                    return LearningPathService::getCohortsForLearningPath();
                }

                public static function assignCohortToLearningPath(int $lptId, int $cohortId): bool
                {
                    return LearningPathService::assignCohortToLearningPath($lptId, $cohortId);
                }

                public static function getLearningPathEnrolledCohorts(int $lptId): array
                {
                    return LearningPathService::getLearningPathEnrolledCohorts($lptId);
                }

                public static function unassignCohortFromLearningPath(int $lptId, int $cohortId): bool
                {
                    return LearningPathService::unassignCohortFromLearningPath($lptId, $cohortId);
                }

                public static function assignUserToLearningPath(int $lptId, int $userId, int $assigneeId = null): bool
                {
                    return LearningPathService::assignUserToLearningPath($lptId, $userId, $assigneeId);
                }

                public static function unassignUserFromLearningPath(int $lptId, int $userId): bool
                {
                    return LearningPathService::unassignUserFromLearningPath($lptId, $userId);
                }

                // ========== Organization Structure ==========

                public static function getOrganizationUnits(): array
                {
                    return OrganizationService::getOrganizationUnits();
                }

                public static function getOrganizationTree(): array
                {
                    return OrganizationService::getOrganizationTree();
                }

                public static function getOrganizationUnit(int $id): ?object
                {
                    return OrganizationService::getOrganizationUnit($id);
                }

                public static function createOrganizationUnit(array $data): int
                {
                    return OrganizationService::createOrganizationUnit($data);
                }

                public static function updateOrganizationUnit(int $id, array $data): bool
                {
                    return OrganizationService::updateOrganizationUnit($id, $data);
                }

                public static function deleteOrganizationUnit(int $id): bool
                {
                    return OrganizationService::deleteOrganizationUnit($id);
                }

                public static function getOrganizationUsers(int $orgId): array
                {
                    return OrganizationService::getOrganizationUsers($orgId);
                }

                public static function getUserOrganizations(int $userId): array
                {
                    return OrganizationService::getUserOrganizations($userId);
                }

                public static function addUserToOrganization(int $orgId, int $userId, string $role = 'member'): bool
                {
                    return OrganizationService::addUserToOrganization($orgId, $userId, $role);
                }

                public static function removeUserFromOrganization(int $orgId, int $userId): bool
                {
                    return OrganizationService::removeUserFromOrganization($orgId, $userId);
                }

                public static function getAllOrganizationUsers(int $orgId): array
                {
                    return OrganizationService::getAllOrganizationUsers($orgId);
                }
                
                public static function getAvailableOrganizationUsers(int $orgId): array
                {
                    return OrganizationService::getAvailableOrganizationUsers($orgId);
                }
                
                public static function getOrganizationUserCount(int $orgId): int
                {
                    return OrganizationService::getOrganizationUserCount($orgId);
                }
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
